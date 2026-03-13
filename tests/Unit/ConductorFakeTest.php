<?php

declare(strict_types=1);

use Conductor\Testing\ConductorFake;
use Conductor\Testing\FakeAgentResponse;

it('returns fake responses for registered agents', function () {
    $fake = new ConductorFake([
        'my-agent' => 'Hello from fake!',
    ]);

    $response = $fake->agent('my-agent')->run('test');

    expect($response->text())->toBe('Hello from fake!');
});

it('returns empty response for unregistered agents', function () {
    $fake = new ConductorFake;

    $response = $fake->agent('unknown')->run('test');

    expect($response->text())->toBe('');
});

it('supports wildcard responses', function () {
    $fake = new ConductorFake([
        '*' => 'default response',
    ]);

    $response = $fake->agent('any-agent')->run('test');

    expect($response->text())->toBe('default response');
});

it('supports callable responses', function () {
    $fake = new ConductorFake([
        'dynamic' => fn (string $input) => "Echo: {$input}",
    ]);

    $response = $fake->agent('dynamic')->run('hello');

    expect($response->text())->toBe('Echo: hello');
});

it('supports sequence responses', function () {
    $fake = new ConductorFake([
        'seq-agent' => ConductorFake::sequence(['First', 'Second', 'Third']),
    ]);

    expect($fake->agent('seq-agent')->run('1')->text())->toBe('First')
        ->and($fake->agent('seq-agent')->run('2')->text())->toBe('Second')
        ->and($fake->agent('seq-agent')->run('3')->text())->toBe('Third');
});

it('supports FakeAgentResponse objects', function () {
    $fakeResponse = new FakeAgentResponse(
        text: 'Custom fake',
        promptTokens: 42,
        completionTokens: 18,
    );

    $fake = new ConductorFake([
        'custom' => $fakeResponse,
    ]);

    $response = $fake->agent('custom')->run('test');

    expect($response->text())->toBe('Custom fake')
        ->and($response->promptTokens())->toBe(42);
});

it('records interactions', function () {
    $fake = new ConductorFake([
        'recorder' => 'recorded response',
    ]);

    $fake->agent('recorder')->run('input 1');
    $fake->agent('recorder')->run('input 2');

    $interactions = $fake->getInteractions();

    expect($interactions)->toHaveCount(2)
        ->and($interactions[0]->agentName)->toBe('recorder')
        ->and($interactions[0]->input)->toBe('input 1')
        ->and($interactions[1]->input)->toBe('input 2');
});
