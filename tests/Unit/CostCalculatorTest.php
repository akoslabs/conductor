<?php

declare(strict_types=1);

use Conductor\Monitoring\CostCalculator;

it('calculates cost for claude-sonnet-4-20250514', function () {
    $cost = CostCalculator::calculate('claude-sonnet-4-20250514', 1_000_000, 1_000_000);

    expect($cost)->toBe(18.0); // $3 input + $15 output
});

it('calculates cost for gpt-4o', function () {
    $cost = CostCalculator::calculate('gpt-4o', 1_000_000, 1_000_000);

    expect($cost)->toBe(12.5); // $2.50 input + $10 output
});

it('returns zero for unknown models', function () {
    $cost = CostCalculator::calculate('unknown-model', 1000, 500);

    expect($cost)->toBe(0.0);
});

it('calculates fractional costs correctly', function () {
    $cost = CostCalculator::calculate('claude-sonnet-4-20250514', 100, 50);

    // 100/1M * $3 = 0.0003, 50/1M * $15 = 0.00075
    expect($cost)->toBeGreaterThan(0.0)
        ->and($cost)->toBeLessThan(0.01);
});
