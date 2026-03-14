<?php

declare(strict_types=1);

use Conductor\Agents\Agent;
use Conductor\Contracts\AgentResponseInterface;
use Prism\Prism\Prism;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\ValueObjects\Meta;
use Prism\Prism\ValueObjects\Usage;

beforeEach(function () {
    $this->artisan('migrate')->assertSuccessful();
});

it('can run a class-based agent', function () {
    $agentClass = new class extends Agent
    {
        protected string $name = 'class-agent';

        protected string $provider = 'anthropic';

        protected string $model = 'claude-sonnet-4-20250514';

        public function systemPrompt(): string
        {
            return 'You are a class-based agent.';
        }
    };

    app()->instance($agentClass::class, $agentClass);

    Prism::fake([
        TextResponseFake::make()
            ->withText('I am a class-based agent!')
            ->withUsage(new Usage(30, 15))
            ->withMeta(new Meta('id', 'claude-sonnet-4-20250514')),
    ]);

    $response = $agentClass::run('Hello');

    expect($response)->toBeInstanceOf(AgentResponseInterface::class)
        ->and($response->text())->toBe('I am a class-based agent!');
});
