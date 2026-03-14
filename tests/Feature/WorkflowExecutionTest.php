<?php

declare(strict_types=1);

use Conductor\Events\WorkflowCompleted;
use Conductor\Events\WorkflowStarted;
use Conductor\Events\WorkflowStepCompleted;
use Conductor\Facades\Conductor;
use Conductor\Workflows\WorkflowState;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->artisan('migrate')->assertSuccessful();
});

it('executes a multi-step workflow', function () {
    Event::fake([WorkflowStarted::class, WorkflowStepCompleted::class, WorkflowCompleted::class]);

    $result = Conductor::workflow('multi-step')
        ->step('analyze', fn (WorkflowState $state) => 'Analysis of: '.$state->input())
        ->step('summarize', fn (WorkflowState $state) => 'Summary: '.$state->getStepOutput('analyze'))
        ->start('Test document');

    expect($result->stepsCompleted())->toBe(2)
        ->and($result->status())->toBe('completed');

    Event::assertDispatched(WorkflowStarted::class);
    Event::assertDispatched(WorkflowStepCompleted::class, 2);
    Event::assertDispatched(WorkflowCompleted::class);
});

it('passes output between steps via state', function () {
    $result = Conductor::workflow('chain')
        ->step('step-1', fn (WorkflowState $state) => 'A')
        ->step('step-2', fn (WorkflowState $state) => $state->getStepOutput('step-1').'B')
        ->step('step-3', fn (WorkflowState $state) => $state->getStepOutput('step-2').'C')
        ->start('input');

    expect($result->stepsCompleted())->toBe(3)
        ->and($result->output()->text())->toBe('ABC');
});
