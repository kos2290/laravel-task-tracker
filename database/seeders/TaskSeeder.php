<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Arr;

class TaskSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < 100; $i++) {
            DB::table('tasks')->insert([
                'title'              => $faker->sentence(),
                'description'        => $faker->paragraph(),
                'status'             => Arr::random(['new', 'in_progress', 'done']),
                'created_by_user_id' => User::all()->random()->id,
                'assigned_user_id'   => User::all()->random()->id,
            ]);
        }
    }
}
