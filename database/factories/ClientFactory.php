<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use src\Clients\Models\Client;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'name' => $firstName,
            'surname' => $lastName,
            'full_name' => "$firstName $lastName",
            'email' => fake()->unique()->safeEmail(),
            'password' => fake()->password(), // password
        ];
    }
}
