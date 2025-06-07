<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'              => $this->faker->sentence,
            'description'        => $this->faker->paragraph,
            'status'             => $this->faker->randomElement(['new', 'in_progress', 'done']),
            'created_by_user_id' => User::all()->random()->id,
            'assigned_user_id'   => User::all()->random()->id,
        ];
    }
}
