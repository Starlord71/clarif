<?php

namespace Database\Factories;

use App\Enums\ReportStatus;
use App\Models\Report;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Generates Report model instances for tests and seeders.
 *
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'original_filename' => fake()->slug().'.sarif',
            'tool_name' => fake()->randomElement(['CodeQL', 'ESLint', 'Semgrep']),
            'tool_driver_version' => fake()->optional()->numerify('#.#.#'),
            'status' => ReportStatus::Pending,
            'error_message' => null,
            'meta' => [
                'total_findings' => fake()->numberBetween(0, 500),
            ],
        ];
    }
}
