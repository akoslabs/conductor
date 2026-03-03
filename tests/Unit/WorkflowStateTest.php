<?php

declare(strict_types=1);

use Conductor\Workflows\WorkflowState;

it('stores and retrieves input', function () {
    $state = new WorkflowState('test input', ['key' => 'value']);

    expect($state->input())->toBe('test input')
        ->and($state->metadata())->toBe(['key' => 'value']);
});

it('manages step outputs', function () {
    $state = new WorkflowState('input');

    expect($state->hasStepOutput('step-1'))->toBeFalse()
        ->and($state->getStepOutput('step-1'))->toBeNull();

    $state->setStepOutput('step-1', 'output-1');

    expect($state->hasStepOutput('step-1'))->toBeTrue()
        ->and($state->getStepOutput('step-1'))->toBe('output-1');
});

it('retrieves all step outputs', function () {
    $state = new WorkflowState('input');

    $state->setStepOutput('step-1', 'a');
    $state->setStepOutput('step-2', 'b');

    expect($state->allStepOutputs())->toBe(['step-1' => 'a', 'step-2' => 'b']);
});

it('serializes and deserializes', function () {
    $state = new WorkflowState('input', ['meta' => 'data']);
    $state->setStepOutput('step-1', 'output');

    $array = $state->toArray();
    $restored = WorkflowState::fromArray($array);

    expect($restored->input())->toBe('input')
        ->and($restored->metadata())->toBe(['meta' => 'data'])
        ->and($restored->getStepOutput('step-1'))->toBe('output');
});
