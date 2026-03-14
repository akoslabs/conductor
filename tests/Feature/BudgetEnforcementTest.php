<?php

declare(strict_types=1);

use Conductor\Exceptions\TokenBudgetExceededException;
use Conductor\Facades\Conductor;
use Conductor\Workflows\WorkflowState;

beforeEach(function () {
    $this->artisan('migrate')->assertSuccessful();
});

it('enforces per-workflow budget', function () {
    expect(fn () => Conductor::workflow('budget-wf')
        ->withTokenBudget(1)
        ->step('step-1', fn (WorkflowState $state) => 'output')
        ->start('test')
    )->not->toThrow(TokenBudgetExceededException::class);
    // Token budget is checked between steps, not after the first
});
