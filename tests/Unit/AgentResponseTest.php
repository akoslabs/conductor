<?php

declare(strict_types=1);

use Conductor\Agents\AgentResponse;
use Prism\Prism\Enums\FinishReason;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\ValueObjects\Meta;
use Prism\Prism\ValueObjects\Usage;

it('wraps a Prism response and exposes text', function () {
    $prismResponse = TextResponseFake::make()
        ->withText('Hello, world!')
        ->withUsage(new Usage(100, 50))
        ->withMeta(new Meta('test-id', 'test-model'))
        ->withFinishReason(FinishReason::Stop);

    $response = new AgentResponse(
        prismResponse: $prismResponse,
        provider: 'anthropic',
        model: 'claude-sonnet-4-20250514',
        durationMs: 250,
        metadata: ['key' => 'value'],
    );

    expect($response->text())->toBe('Hello, world!')
        ->and($response->promptTokens())->toBe(100)
        ->and($response->completionTokens())->toBe(50)
        ->and($response->durationMs())->toBe(250)
        ->and($response->provider())->toBe('anthropic')
        ->and($response->model())->toBe('claude-sonnet-4-20250514')
        ->and($response->metadata())->toBe(['key' => 'value'])
        ->and($response->structured())->toBeNull();
});

it('calculates cost for known models', function () {
    $prismResponse = TextResponseFake::make()
        ->withText('test')
        ->withUsage(new Usage(1000, 500))
        ->withMeta(new Meta('id', 'claude-sonnet-4-20250514'));

    $response = new AgentResponse(
        prismResponse: $prismResponse,
        provider: 'anthropic',
        model: 'claude-sonnet-4-20250514',
        durationMs: 100,
    );

    expect($response->costUsd())->toBeGreaterThan(0.0);
});

it('serializes to array and JSON', function () {
    $prismResponse = TextResponseFake::make()
        ->withText('Hello')
        ->withUsage(new Usage(10, 20))
        ->withMeta(new Meta('id', 'model'));

    $response = new AgentResponse(
        prismResponse: $prismResponse,
        provider: 'test',
        model: 'test-model',
        durationMs: 50,
    );

    $array = $response->toArray();
    expect($array)->toHaveKeys(['text', 'provider', 'model', 'prompt_tokens', 'completion_tokens', 'duration_ms'])
        ->and($array['text'])->toBe('Hello');

    $json = $response->toJson();
    expect(json_decode($json, true))->toHaveKey('text', 'Hello');
});
