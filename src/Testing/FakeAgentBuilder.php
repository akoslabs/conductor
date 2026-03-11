<?php

declare(strict_types=1);

namespace Conductor\Testing;

use Conductor\Contracts\AgentBuilderInterface;
use Conductor\Contracts\AgentResponseInterface;
use Generator;

final class FakeAgentBuilder implements AgentBuilderInterface
{
    /** @var string|AgentResponseInterface|FakeSequence|callable|null */
    private mixed $fakeResponse;

    /**
     * @param  string  $name  The agent name.
     * @param  string|AgentResponseInterface|FakeSequence|callable|null  $response  The fake response.
     * @param  ConductorFake  $recorder  The fake instance for recording.
     */
    public function __construct(
        private readonly string $name,
        mixed $response,
        private readonly ConductorFake $recorder,
    ) {
        $this->fakeResponse = $response;
    }

    /**
     * {@inheritDoc}
     */
    public function using(string $provider, string $model): static
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withSystemPrompt(string $prompt): static
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withTools(array $tools): static
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withMemory(?string $conversationId = null): static
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withFallback(string $provider, string $model): static
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withTokenBudget(int $maxTokens): static
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withSchema(array $schema): static
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withMaxSteps(int $steps): static
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withMetadata(array $metadata): static
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function run(string $input): AgentResponseInterface
    {
        $response = $this->resolveResponse($input);

        $this->recorder->recordInteraction($this->name, $input, $response);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function stream(string $input): Generator
    {
        $response = $this->resolveResponse($input);

        $this->recorder->recordInteraction($this->name, $input, $response);

        yield $response;
    }

    /**
     * Resolve the fake response.
     *
     * @param  string  $input  The user input.
     */
    private function resolveResponse(string $input): AgentResponseInterface
    {
        $response = $this->fakeResponse;

        if ($response instanceof FakeSequence) {
            return $response->next($input);
        }

        if (is_string($response)) {
            return FakeAgentResponse::fromString($response);
        }

        if ($response instanceof AgentResponseInterface) {
            return $response;
        }

        if ($response instanceof \Closure) {
            $result = $response($input);

            return is_string($result) ? FakeAgentResponse::fromString($result) : $result;
        }

        return FakeAgentResponse::fromString('');
    }
}
