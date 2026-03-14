<?php

declare(strict_types=1);

use Conductor\Facades\Conductor;
use Conductor\Models\WorkflowStepRun;
use Conductor\Workflows\WorkflowState;

beforeEach(function () {
    $this->artisan('migrate')->assertSuccessful();
});

it('skips steps when condition is false', function () {
    $result = Conductor::workflow('conditional')
        ->step('classify', fn (WorkflowState $state) => 'positive')
        ->when(
            'handle-positive',
            fn (WorkflowState $state) => $state->getStepOutput('classify') === 'positive',
            fn (WorkflowState $state) => 'handled positive',
        )
        ->when(
            'handle-negative',
            fn (WorkflowState $state) => $state->getStepOutput('classify') === 'negative',
            fn (WorkflowState $state) => 'handled negative',
        )
        ->start('test');

    expect($result->stepsCompleted())->toBe(2) // classify + handle-positive
        ->and($result->status())->toBe('completed');

    $skipped = WorkflowStepRun::where('step_name', 'handle-negative')
        ->where('status', 'skipped')
        ->exists();

    expect($skipped)->toBeTrue();
});
