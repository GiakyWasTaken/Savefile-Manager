<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Testing\File;
use App\Models\Savefile;
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
            Savefile::create([
                'id' => $i,
                'file_name' => $file_name,
                'created_at' => $faker->dateTimeThisYear,
                'updated_at' => $faker->dateTimeThisYear,
                'fk_id_game' => $faker->numberBetween(1, 10),
            ]);
            // Create the file on the server
            $file = File::fake()->create($file_name);
            Storage::putFileAs(
                'saves/',
                $file,
                $file_name
            );
            // Append some text to the file
            Storage::prepend('saves/' . $file_name, $faker->text(100));
            // Create backup file
            Storage::copy('saves/' . $file_name, 'backups/' . $file_name . '_' . date('Y_m_d_His') . '.bak');
        }
    }
}
