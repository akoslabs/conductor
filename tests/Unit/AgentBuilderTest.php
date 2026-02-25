<?php

declare(strict_types=1);

use Conductor\Agents\AgentBuilder;
use Conductor\Contracts\AgentResponseInterface;
use Conductor\Events\AgentCompleted;
use Conductor\Events\AgentStarted;
use Illuminate\Support\Facades\Event;
use Prism\Prism\Prism;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\ValueObjects\Meta;
use Prism\Prism\ValueObjects\Usage;

beforeEach(function () {
    $this->artisan('migrate')->assertSuccessful();
});

it('builds and runs an agent via Prism::fake()', function () {
    Event::fake([AgentStarted::class, AgentCompleted::class]);

    Prism::fake([
        TextResponseFake::make()
            ->withText('Hello from Prism!')
            ->withUsage(new Usage(50, 25))
            ->withMeta(new Meta('test-id', 'claude-sonnet-4-20250514')),
    ]);

    $builder = new AgentBuilder('test-agent');
    $response = $builder
        ->using('anthropic', 'claude-sonnet-4-20250514')
        ->withSystemPrompt('You are helpful')
        ->run('Hello');

    expect($response)->toBeInstanceOf(AgentResponseInterface::class)
        ->and($response->text())->toBe('Hello from Prism!')
        ->and($response->promptTokens())->toBe(50)
        ->and($response->completionTokens())->toBe(25)
        ->and($response->provider())->toBe('anthropic')
        ->and($response->model())->toBe('claude-sonnet-4-20250514')
        ->and($response->durationMs())->toBeGreaterThanOrEqual(0);

    Event::assertDispatched(AgentStarted::class);
    Event::assertDispatched(AgentCompleted::class);
});

it('uses default provider and model from config', function () {
    Prism::fake([
        TextResponseFake::make()
            ->withText('default response')
            ->withUsage(new Usage(10, 5))
            ->withMeta(new Meta('id', 'model')),
    ]);

    $builder = new AgentBuilder('default-agent');
    $response = $builder->run('test');

    expect($response->text())->toBe('default response');
});

it('chains fluent methods', function () {
    Prism::fake([
        TextResponseFake::make()
            ->withText('ok')
            ->withUsage(new Usage(10, 5))
            ->withMeta(new Meta('id', 'model')),
    ]);

    $builder = new AgentBuilder('fluent-agent');
    $response = $builder
        ->using('openai', 'gpt-4o')
        ->withSystemPrompt('System prompt')
        ->withMaxSteps(3)
        ->withMetadata(['key' => 'value'])
        ->run('test');

    expect($response->text())->toBe('ok')
        ->and($response->metadata())->toBe(['key' => 'value']);
});
