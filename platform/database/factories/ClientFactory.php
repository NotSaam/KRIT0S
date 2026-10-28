<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'sector' => fake()->randomElement(['Banca', 'Salud', 'Logística', 'Retail', 'Energía']),
            'contact_name' => fake()->name(),
            'contact_email' => fake()->companyEmail(),
        ];
    }
}
