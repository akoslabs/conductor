<?php

declare(strict_types=1);

use Conductor\Facades\Conductor;
use Conductor\Models\WorkflowRun;
use Conductor\Models\WorkflowStepRun;
use Conductor\Workflows\WorkflowState;

beforeEach(function () {
    $this->artisan('migrate')->assertSuccessful();
});

it('persists workflow run and step run records', function () {
    Conductor::workflow('persisted-wf')
        ->step('step-a', fn (WorkflowState $state) => 'output-a')
        ->step('step-b', fn (WorkflowState $state) => 'output-b')
        ->start('test input');

    $run = WorkflowRun::where('workflow_name', 'persisted-wf')->first();

    expect($run)->not->toBeNull()
        ->and($run->status)->toBe('completed')
        ->and($run->total_tokens)->toBeGreaterThanOrEqual(0);

    $stepRuns = WorkflowStepRun::where('workflow_run_id', $run->id)
        ->where('status', 'completed')
        ->get();

    expect($stepRuns)->toHaveCount(2);
});
