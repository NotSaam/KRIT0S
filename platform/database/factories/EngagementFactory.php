<?php

namespace Database\Factories;

use App\Enums\EngagementStatus;
use App\Enums\EngagementType;
use App\Models\Client;
use App\Models\Engagement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Engagement>
 */
class EngagementFactory extends Factory
{
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-3 months', 'now');

        return [
            'client_id' => Client::factory(),
            'lead_id' => User::factory(),
            'code' => 'KRT-'.now()->year.'-'.Str::upper(Str::random(5)),
            'title' => 'Auditoría '.fake()->words(3, true),
            'type' => fake()->randomElement(EngagementType::cases()),
            'status' => EngagementStatus::InProgress,
            'scope' => fake()->paragraph(),
            'methodology' => 'OWASP WSTG v4.2',
            'starts_on' => $start,
            'ends_on' => (clone $start)->modify('+10 days'),
            'executive_summary' => fake()->paragraph(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => EngagementStatus::Delivered,
            'published_at' => now(),
        ]);
    }
}
