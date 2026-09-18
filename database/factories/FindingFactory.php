<?php

namespace Database\Factories;

use App\Enums\SarifLevel;
use App\Models\Finding;
use App\Models\Report;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Generates Finding model instances for tests and seeders.
 *
 * @extends Factory<Finding>
 */
class FindingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ruleId = fake()->bothify('rule-####');
        $filePath = 'src/'.fake()->word().'/'.fake()->word().'.php';
        $line = fake()->numberBetween(1, 5000);
        $severity = fake()->randomElement(SarifLevel::cases());
        $message = fake()->sentence();

        return [
            'report_id' => Report::factory(),
            'rule_id' => $ruleId,
            'file_path' => $filePath,
            'line' => $line,
            'severity' => $severity,
            'message' => $message,
            'fingerprint' => hash('sha256', "{$ruleId}|{$filePath}|{$line}"),
            'payload' => [
                'ruleId' => $ruleId,
                'level' => $severity->value,
                'message' => ['text' => $message],
                'locations' => [
                    [
                        'physicalLocation' => [
                            'artifactLocation' => ['uri' => $filePath],
                            'region' => ['startLine' => $line],
                        ],
                    ],
                ],
            ],
        ];
    }
}
