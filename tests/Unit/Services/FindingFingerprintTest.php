<?php

namespace Tests\Unit\Services;

use App\Services\Sarif\FindingFingerprint;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the pure finding fingerprint.
 */
class FindingFingerprintTest extends TestCase
{
    private FindingFingerprint $fingerprint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fingerprint = new FindingFingerprint;
    }

    public function test_it_matches_the_documented_sha256_formula(): void
    {
        $expected = hash('sha256', 'no-unused-vars|src/index.js|12');

        $this->assertSame($expected, $this->fingerprint->generate('no-unused-vars', 'src/index.js', 12));
    }

    public function test_it_is_stable_for_the_same_input(): void
    {
        $first = $this->fingerprint->generate('rule-1', 'src/a.js', 5);
        $second = $this->fingerprint->generate('rule-1', 'src/a.js', 5);

        $this->assertSame($first, $second);
    }

    public function test_line_changes_produce_a_different_fingerprint(): void
    {
        $this->assertNotSame(
            $this->fingerprint->generate('rule-1', 'src/a.js', 5),
            $this->fingerprint->generate('rule-1', 'src/a.js', 6),
        );
    }

    public function test_a_missing_line_is_still_deterministic(): void
    {
        $this->assertSame(
            hash('sha256', 'rule-1|src/a.js|'),
            $this->fingerprint->generate('rule-1', 'src/a.js', null),
        );
    }
}
