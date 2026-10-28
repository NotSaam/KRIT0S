<?php

namespace Database\Factories;

use App\Models\FindingTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FindingTemplate>
 */
class FindingTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'category' => 'A03:2021 Injection',
            'cvss_vector' => 'CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H',
            'description' => fake()->paragraph(),
            'impact' => fake()->sentence(),
            'remediation' => fake()->sentence(),
            'references' => 'https://owasp.org/Top10/',
        ];
    }
}
