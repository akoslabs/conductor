<?php

declare(strict_types=1);

use Conductor\Facades\Conductor;
use Conductor\Workflows\WorkflowState;

beforeEach(function () {
    $this->artisan('migrate')->assertSuccessful();
});

it('builds a workflow with fluent steps', function () {
    $result = Conductor::workflow('test-workflow')
        ->step('step-1', fn (WorkflowState $state) => 'output-1')
        ->step('step-2', fn (WorkflowState $state) => 'output-2')
        ->start('input');

    expect($result->stepsCompleted())->toBe(2)
        ->and($result->status())->toBe('completed');
});

it('supports conditional steps', function () {
    $result = Conductor::workflow('conditional-wf')
        ->step('step-1', fn (WorkflowState $state) => 'first')
        ->when(
            'step-2',
            fn (WorkflowState $state) => $state->getStepOutput('step-1') === 'first',
            fn (WorkflowState $state) => 'second',
        )
        ->when(
            'step-3',
            fn (WorkflowState $state) => $state->getStepOutput('step-1') === 'nope',
            fn (WorkflowState $state) => 'should be skipped',
        )
        ->start('input');

    expect($result->stepsCompleted())->toBe(2);
});
