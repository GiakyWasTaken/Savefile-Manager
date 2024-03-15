<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Testing\File;
use App\Models\Savefile;
use App\Models\Console;

class SavefileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();
        for ($i = 1; $i <= 10; $i++) {
            // Save a file on the database
            $file_name = $faker->word . '.' . $faker->fileExtension;
            $file_path = $faker->word . '/' . $faker->word . '/';
            $fk_id_console = $faker->numberBetween(1, 10);
            Savefile::create([
                'id' => $i,
                'file_name' => $file_name,
                'file_path' => $file_path,
                'created_at' => $faker->dateTimeThisYear,
                'updated_at' => $faker->dateTimeThisYear,
                'fk_id_console' => $fk_id_console,
            ]);
            // Create the file on the server
            $file = File::fake()->create($file_name, 256);
            // Get the directory from the console
            $console = Console::find($fk_id_console);
            $savefile_directory = 'saves/' . $console->console_name . '/' . $file_path;
            Storage::putFileAs(
                $savefile_directory,
                $file,
                $file_name
            );
            // Append some text to the file
            Storage::prepend($savefile_directory . $file_name, $faker->text(100));
            // Create backup file
            Storage::copy($savefile_directory . $file_name, $savefile_directory . 'backups/' . $file_name . '_' . date('Y_m_d_His') . '.bak');
        }
    }
}
