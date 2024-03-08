<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Savefile>
 */
class SavefileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // // Create a file on the server
        // $file_name = $this->faker->word() . '.' . $this->faker->fileExtension();
        // $file = File::fake()->create($file_name);
        // Storage::putFileAs(
        //     'saves/',
        //     $file,
        //     $file_name
        // );
        return [
            // 'file_name' => $file_name,
            'file_name' => $this->faker->word() . '.' . $this->faker->fileExtension(),
            'fk_id_game' => $this->faker->numberBetween(1, 10),
        ];
    }
}
