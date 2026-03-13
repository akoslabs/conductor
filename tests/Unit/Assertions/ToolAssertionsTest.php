<?php

declare(strict_types=1);

use Conductor\Testing\ConductorFake;
use Conductor\Testing\FakeAgentResponse;
use PHPUnit\Framework\ExpectationFailedException;

it('asserts tool was used', function () {
    $fake = new ConductorFake([
        'tool-agent' => new FakeAgentResponse(
            text: 'result',
            toolCalls: [
                ['name' => 'search', 'arguments' => ['q' => 'test'], 'result' => 'found'],
            ],
        ),
    ]);

    $fake->agent('tool-agent')->run('test');

    $fake->assertToolUsed('search');
    $fake->assertToolUsed('search', 1);
});

it('fails when asserting unused tool', function () {
    $fake = new ConductorFake(['agent' => 'no tools used']);
    $fake->agent('agent')->run('test');

    expect(fn () => $fake->assertToolUsed('nonexistent'))
        ->toThrow(ExpectationFailedException::class);
});
