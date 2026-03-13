<?php

declare(strict_types=1);

use Conductor\Testing\FakeAgentResponse;

it('creates from string', function () {
    $response = FakeAgentResponse::fromString('Hello');

    expect($response->text())->toBe('Hello')
        ->and($response->provider())->toBe('fake')
        ->and($response->model())->toBe('fake-model')
        ->and($response->promptTokens())->toBe(10)
        ->and($response->completionTokens())->toBe(20);
});

it('creates from array', function () {
    $response = FakeAgentResponse::fromArray([
        'text' => 'Custom text',
        'prompt_tokens' => 100,
        'completion_tokens' => 200,
        'cost_usd' => 0.5,
        'provider' => 'openai',
        'model' => 'gpt-4o',
    ]);

    expect($response->text())->toBe('Custom text')
        ->and($response->promptTokens())->toBe(100)
        ->and($response->completionTokens())->toBe(200)
        ->and($response->costUsd())->toBe(0.5)
        ->and($response->provider())->toBe('openai')
        ->and($response->model())->toBe('gpt-4o');
});

it('has sensible defaults', function () {
    $response = new FakeAgentResponse;

    expect($response->text())->toBe('')
        ->and($response->toolCalls())->toBe([])
        ->and($response->structured())->toBeNull()
        ->and($response->metadata())->toBe([]);
});

it('serializes to array and JSON', function () {
    $response = FakeAgentResponse::fromString('Test');

    $array = $response->toArray();
    expect($array)->toHaveKeys(['text', 'provider', 'model', 'prompt_tokens']);

    $json = $response->toJson();
    $decoded = json_decode($json, true);
    expect($decoded['text'])->toBe('Test');
});
