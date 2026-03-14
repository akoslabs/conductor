<?php

declare(strict_types=1);

use Conductor\Events\WorkflowPaused;
use Conductor\Facades\Conductor;
use Conductor\Workflows\WorkflowState;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->artisan('migrate')->assertSuccessful();
});

it('pauses for human approval and resumes', function () {
    Event::fake([WorkflowPaused::class]);

    $result = Conductor::workflow('approval-wf')
        ->humanApproval('review', fn (WorkflowState $state) => 'Needs review: '.$state->input())
        ->step('finalize', fn (WorkflowState $state) => 'Final: '.$state->getStepOutput('review'))
        ->start('Document X');

    expect($result->status())->toBe('paused')
        ->and($result->stepsCompleted())->toBe(1)
        ->and($result->runId())->not->toBeNull();

    Event::assertDispatched(WorkflowPaused::class);

    // Resume the workflow
    $finalResult = Conductor::workflow('approval-wf')
        ->humanApproval('review', fn (WorkflowState $state) => 'Needs review: '.$state->input())
        ->step('finalize', fn (WorkflowState $state) => 'Final: '.$state->getStepOutput('review'))
        ->resume($result->runId(), ['approval' => true]);

    expect($finalResult->status())->toBe('completed');
});
