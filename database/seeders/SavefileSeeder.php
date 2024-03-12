<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Testing\File;
use App\Models\Savefile;
use App\Models\Game;

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
            $fk_id_game = $faker->numberBetween(1, 10);
            Savefile::create([
                'id' => $i,
                'file_name' => $file_name,
                'created_at' => $faker->dateTimeThisYear,
                'updated_at' => $faker->dateTimeThisYear,
                'fk_id_game' => $fk_id_game,
            ]);
            // Create the file on the server
            $file = File::fake()->create($file_name, 256);
            // Get the directory from the game
            $game = Game::find($fk_id_game);
            $savefile_directory = 'saves/' . $game->name . '/';
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
