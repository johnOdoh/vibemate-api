<?php

namespace Database\Factories;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => fake()->userName(),
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'dob' => $this->faker->date('Y-m-d', '2005-01-01'), // Random DOB before 2005
            'here_for' => implode(', ', $this->faker->randomElements(['Friends', 'Networking', 'Dating', 'Hookup'], rand(1, 3))),
            'gender' => Arr::random(['Male', 'Female', 'Other']),
            'phone' => $this->faker->phoneNumber,
            'connect_with' => $this->faker->randomElement(['Male', 'Female', 'Everyone']), // Array of values
            'meta_location' => $this->faker->randomElement(['England', 'USA', 'Nigeria', 'India', 'Russia', 'Brazil', 'Japan', 'Germany', 'France']),
            'image' => $this->faker->imageUrl(200, 200, 'people'), // Fake image URL
            'city' => $this->faker->city,
            'country' => $this->faker->country,
        ];
    }
}
