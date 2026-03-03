<?php

declare(strict_types=1);

use Conductor\Workflows\WorkflowStep;

it('creates a step with defaults', function () {
    $step = new WorkflowStep(
        name: 'my-step',
        callable: fn () => 'result',
    );

    expect($step->name)->toBe('my-step')
        ->and($step->dependsOn)->toBe([])
        ->and($step->retries)->toBe(0)
        ->and($step->backoffMs)->toBe(1000)
        ->and($step->requiresHumanApproval)->toBeFalse()
        ->and($step->condition)->toBeNull();
});

it('creates a step with custom config', function () {
    $condition = fn () => true;

    $step = new WorkflowStep(
        name: 'configured-step',
        callable: fn () => 'result',
        dependsOn: ['step-1', 'step-2'],
        retries: 5,
        backoffMs: 2000,
        requiresHumanApproval: true,
        condition: $condition,
    );

    expect($step->name)->toBe('configured-step')
        ->and($step->dependsOn)->toBe(['step-1', 'step-2'])
        ->and($step->retries)->toBe(5)
        ->and($step->backoffMs)->toBe(2000)
        ->and($step->requiresHumanApproval)->toBeTrue()
        ->and($step->condition)->toBe($condition);
});
