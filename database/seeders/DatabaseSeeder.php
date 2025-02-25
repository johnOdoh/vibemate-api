<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Profile;
use App\Models\Interest;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Post::factory()->count(20)->create();
        // Create users with interests
        User::factory(20)->has(Profile::factory())->create()->each(function ($user) {
            $interests = Interest::inRandomOrder()->limit(rand(2, 5))->pluck('id');
            $user->interests()->attach($interests);
        });
    }
}
