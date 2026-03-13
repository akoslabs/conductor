<?php

declare(strict_types=1);

use Conductor\Exceptions\TokenBudgetExceededException;
use Conductor\Monitoring\BudgetEnforcer;

it('passes when tokens are within budget', function () {
    BudgetEnforcer::checkPerRequestBudget('agent', 100, 1000);

    expect(true)->toBeTrue(); // No exception thrown
});

it('throws when per-request budget exceeded', function () {
    expect(fn () => BudgetEnforcer::checkPerRequestBudget('agent', 1500, 1000))
        ->toThrow(TokenBudgetExceededException::class);
});

it('skips check when budget is null', function () {
    BudgetEnforcer::checkPerRequestBudget('agent', 999999, null);

    expect(true)->toBeTrue();
});

it('throws when per-workflow budget exceeded', function () {
    expect(fn () => BudgetEnforcer::checkPerWorkflowBudget('workflow', 5000, 2000))
        ->toThrow(TokenBudgetExceededException::class);
});

it('includes context in exception', function () {
    try {
        BudgetEnforcer::checkPerRequestBudget('my-agent', 150, 100);
        $this->fail('Expected exception');
    } catch (TokenBudgetExceededException $e) {
        expect($e->context)->toBe('my-agent')
            ->and($e->tokensUsed)->toBe(150)
            ->and($e->budget)->toBe(100);
    }
});
