<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Todo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $activity = Activity::factory()->count(10)
            ->has(Todo::factory(5))
            ->create();
    }
}