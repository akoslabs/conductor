<?php

declare(strict_types=1);

use Conductor\ConductorManager;
use Conductor\Facades\Conductor;
use Conductor\Testing\ConductorFake;

it('replaces ConductorManager with ConductorFake', function () {
    $fake = Conductor::fake([
        'test-agent' => 'Faked response!',
    ]);

    expect($fake)->toBeInstanceOf(ConductorFake::class);
    expect(app(ConductorManager::class))->toBeInstanceOf(ConductorFake::class);

    $response = Conductor::agent('test-agent')->run('test input');

    expect($response->text())->toBe('Faked response!');

    Conductor::assertAgentCalled('test-agent');
    Conductor::assertAgentCalled('test-agent', 1);
    Conductor::assertAgentNotCalled('other-agent');
    Conductor::assertTokensBelow(1000);
});

it('supports sequences via Conductor::fake()', function () {
    Conductor::fake([
        'seq' => ConductorFake::sequence(['First', 'Second']),
    ]);

    expect(Conductor::agent('seq')->run('1')->text())->toBe('First')
        ->and(Conductor::agent('seq')->run('2')->text())->toBe('Second');

    Conductor::assertAgentCalled('seq', 2);
});

it('supports callable fakes', function () {
    Conductor::fake([
        'echo' => fn (string $input) => "Echo: {$input}",
    ]);

    $response = Conductor::agent('echo')->run('hello world');

    expect($response->text())->toBe('Echo: hello world');
});
