<?php

namespace Database\Factories;

use App\Models\Console;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        return [
            'file_name' => $this->faker->word() . '.' . $this->faker->fileExtension(),
            'file_path' => 'saves/' . $this->faker->word() . '.' . $this->faker->fileExtension(),
            'fk_id_console' => Console::factory(),
        ];
    }
}
