<?php

namespace Database\Factories;

use App\Enums\FindingStatus;
use App\Models\Engagement;
use App\Models\Finding;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Finding>
 */
class FindingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'engagement_id' => Engagement::factory(),
            'title' => fake()->sentence(5),
            'cvss_vector' => fake()->randomElement([
                'CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H',
                'CVSS:3.1/AV:N/AC:L/PR:N/UI:R/S:C/C:L/I:L/A:N',
                'CVSS:3.1/AV:N/AC:H/PR:L/UI:N/S:U/C:L/I:N/A:N',
            ]),
            'status' => FindingStatus::Open,
            'affected_assets' => 'https://app.example.test/login',
            'description' => fake()->paragraph(),
            'impact' => fake()->sentence(),
            'remediation' => fake()->sentence(),
        ];
    }
}
