<?php

declare(strict_types=1);

use Conductor\Exceptions\WorkflowException;
use Conductor\Facades\Conductor;
use Conductor\Workflows\WorkflowState;

beforeEach(function () {
    $this->artisan('migrate')->assertSuccessful();
});

it('retries a step on failure then succeeds', function () {
    $attempts = 0;

    $result = Conductor::workflow('retry-wf')
        ->step('flaky-step', function (WorkflowState $state) use (&$attempts) {
            $attempts++;
            if ($attempts < 3) {
                throw new RuntimeException('Temporary failure');
            }

            return 'Success on attempt '.$attempts;
        }, retries: 3, backoffMs: 1)
        ->start('test');

    expect($result->stepsCompleted())->toBe(1)
        ->and($result->status())->toBe('completed')
        ->and($attempts)->toBe(3);
});

it('fails after exhausting retries', function () {
    expect(fn () => Conductor::workflow('fail-wf')
        ->step('always-fails', function () {
            throw new RuntimeException('Always fails');
        }, retries: 1, backoffMs: 1)
        ->start('test')
    )->toThrow(WorkflowException::class);
});
