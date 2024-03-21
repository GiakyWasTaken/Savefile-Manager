<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\Fluent\AssertableJson;
use Faker\Factory;
use Illuminate\Support\Facades\Storage;
use App\Models\Savefile;
use App\Models\Console;
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

    public function test_get_savefile_json(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->get('/api/savefile/1', ['Accept' => 'application/json']);

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('id', 1)
                    ->etc()
            );
    }

    public function test_store_savefile(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $faker = Factory::create();

        // Create a file
        $file = UploadedFile::fake()->create('savefile.txt', 256);
        $file_name = $faker->word . '.' . $faker->fileExtension;
        $file_path = $faker->word . '/' . $faker->word . '/';
        $updated_at = date('Y-m-d\TH:i:s.u\Z');
        $fk_id_console = strval($faker->numberBetween(1, 10));

        // Send the request
        $response = $this->post('/api/savefile', [
            'savefile' => $file,
            'file_name' => $file_name,
            'file_path' => $file_path,
            'updated_at' => $updated_at,
            'fk_id_console' => $fk_id_console
        ]);

        // Check the response
        $response
            ->assertStatus(201)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('file_name', $file_name)
                    ->where('file_path', $file_path)
                    ->where('updated_at', $updated_at)
                    ->where('fk_id_console', $fk_id_console)
                    ->etc()
            );

        // Get the directory from the console and the file path
        $console = Console::find($fk_id_console);
        $savefile_dir = 'saves/' . $console->console_name . '/' . $file_path;

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
        $this->assertFileExists(storage_path('app/' . $savefile_dir . $file_name));
        $this->assertFileExists(storage_path('app/' . $backup_file_path));

        // Check if the file and the backup are the same
        $this->assertFileEquals(
            $file->getPathname(),
            storage_path('app/' . $savefile_dir . $file_name),
            storage_path('app/' . $backup_file_path)
        );

        // Delete the file and the backup
        Storage::delete($savefile_dir . $file_name);
        Storage::delete($backup_file_path);
    }

    public function test_store_savefile_no_file_name_and_path(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $faker = Factory::create();

        // Create a file
        $file_name = $faker->word . '.' . $faker->fileExtension;
        $file = UploadedFile::fake()->create($file_name, 256);
        $fk_id_console = strval($faker->numberBetween(1, 10));

        // Send the request
        $response = $this->post('/api/savefile', [
            'savefile' => $file,
            'fk_id_console' => $fk_id_console
        ]);

        // Check the response
        $response
            ->assertStatus(201)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('file_name', $file_name)
                    ->where('file_path', '/')
                    ->where('fk_id_console', $fk_id_console)
                    ->etc()
            );

        // Get the directory from the console
        $console = Console::find($fk_id_console);
        $savefile_dir = 'saves/' . $console->console_name . '/';

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
        $this->assertFileExists(storage_path('app/' . $savefile_dir . $file_name));
        $this->assertFileExists(storage_path('app/' . $backup_file_path));

        // Check if the file and the backup are the same
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
        $file_path = $savefile->file_path;
        $updated_at = date('Y-m-d\TH:i:s.u\Z');
        $fk_id_console = $savefile->fk_id_console;

        // Get the directory from the console
        $console = Console::find($fk_id_console);
        $savefile_dir = 'saves/' . $console->console_name . '/' . $file_path;

        // Save the previous file
        Storage::copy($savefile_dir . $file_name, $savefile_dir . $file_name . '.bak');

        // Send the request
        $response = $this->put('/api/savefile/1', [
            'savefile' => $file,
            'updated_at' => $updated_at
        ]);

        // Check the response
        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('file_name', $file_name)
                    ->where('file_path', $file_path)
                    ->where('updated_at', $updated_at)
                    ->where('fk_id_console', $fk_id_console)
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
        $file_path = $savefile->file_path;
        $fk_id_console = $savefile->fk_id_console;
        $console = Console::find($fk_id_console);
        $savefile_dir = 'saves/' . $console->console_name . '/' . $file_path;
        Storage::copy($savefile_dir . $file_name, $savefile_dir . $file_name . '.bak');

        // Test the deletion
        $response = $this->delete('/api/savefile/1');
        $response->assertStatus(200);
        $this->assertFileDoesNotExist('app/' . $savefile_dir . $file_name);

        // Restore the file
        Storage::move($savefile_dir . $file_name . '.bak', $savefile_dir . $file_name);
    }

}
