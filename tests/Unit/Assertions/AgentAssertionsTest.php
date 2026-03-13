<?php

declare(strict_types=1);

use Conductor\Testing\ConductorFake;
use PHPUnit\Framework\ExpectationFailedException;

it('asserts agent was called', function () {
    $fake = new ConductorFake(['agent-a' => 'response']);
    $fake->agent('agent-a')->run('test');

    $fake->assertAgentCalled('agent-a');
    $fake->assertAgentCalled('agent-a', 1);
});

it('fails when asserting uncalled agent', function () {
    $fake = new ConductorFake;

    expect(fn () => $fake->assertAgentCalled('never-called'))
        ->toThrow(ExpectationFailedException::class);
});

it('asserts agent was not called', function () {
    $fake = new ConductorFake;

    $fake->assertAgentNotCalled('uncalled');
});

it('fails when asserting not called but was called', function () {
    $fake = new ConductorFake(['called' => 'response']);
    $fake->agent('called')->run('test');

    expect(fn () => $fake->assertAgentNotCalled('called'))
        ->toThrow(ExpectationFailedException::class);
});

it('asserts nothing was called', function () {
    $fake = new ConductorFake;

    $fake->assertNothingCalled();
});

it('fails asserting nothing when something was called', function () {
    $fake = new ConductorFake(['agent' => 'response']);
    $fake->agent('agent')->run('test');

    expect(fn () => $fake->assertNothingCalled())
        ->toThrow(ExpectationFailedException::class);
});
