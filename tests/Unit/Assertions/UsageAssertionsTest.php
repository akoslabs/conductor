<?php

declare(strict_types=1);

use Conductor\Testing\ConductorFake;
use Conductor\Testing\FakeAgentResponse;
use PHPUnit\Framework\ExpectationFailedException;

it('asserts tokens below threshold', function () {
    $fake = new ConductorFake([
        'agent' => new FakeAgentResponse(text: 'ok', promptTokens: 10, completionTokens: 20),
    ]);

    $fake->agent('agent')->run('test');

    $fake->assertTokensBelow(100);
});

it('fails when tokens exceed threshold', function () {
    $fake = new ConductorFake([
        'agent' => new FakeAgentResponse(text: 'ok', promptTokens: 500, completionTokens: 600),
    ]);

    $fake->agent('agent')->run('test');

    expect(fn () => $fake->assertTokensBelow(100))
        ->toThrow(ExpectationFailedException::class);
});
