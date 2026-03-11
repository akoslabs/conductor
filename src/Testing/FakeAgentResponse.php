<?php

declare(strict_types=1);

namespace Conductor\Testing;

use Conductor\Contracts\AgentResponseInterface;

final class FakeAgentResponse implements AgentResponseInterface
{
    /**
     * @param  string  $text  The response text.
     * @param  int  $promptTokens  The prompt token count.
     * @param  int  $completionTokens  The completion token count.
     * @param  float  $costUsd  The estimated cost.
     * @param  int  $durationMs  The duration.
     * @param  array<int, array{name: string, arguments: array, result: mixed}>  $toolCalls  The tool calls.
     * @param  array<string, mixed>|null  $structured  The structured output.
     * @param  string  $provider  The provider.
     * @param  string  $model  The model.
     * @param  array<string, mixed>  $metadata  The metadata.
     */
    public function __construct(
        private readonly string $text = '',
        private readonly int $promptTokens = 10,
        private readonly int $completionTokens = 20,
        private readonly float $costUsd = 0.001,
        private readonly int $durationMs = 100,
        private readonly array $toolCalls = [],
        private readonly ?array $structured = null,
        private readonly string $provider = 'fake',
        private readonly string $model = 'fake-model',
        private readonly array $metadata = [],
    ) {}

    /**
     * Create a fake response from a string.
     *
     * @param  string  $text  The response text.
     */
    public static function fromString(string $text): self
    {
        return new self(text: $text);
    }

    /**
     * Create a fake response from an array.
     *
     * @param  array<string, mixed>  $data  The response data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            text: $data['text'] ?? '',
            promptTokens: $data['prompt_tokens'] ?? 10,
            completionTokens: $data['completion_tokens'] ?? 20,
            costUsd: $data['cost_usd'] ?? 0.001,
            durationMs: $data['duration_ms'] ?? 100,
            toolCalls: $data['tool_calls'] ?? [],
            structured: $data['structured'] ?? null,
            provider: $data['provider'] ?? 'fake',
            model: $data['model'] ?? 'fake-model',
            metadata: $data['metadata'] ?? [],
        );
    }

    /**
     * {@inheritDoc}
     */
    public function text(): string
    {
        return $this->text;
    }

    /**
     * {@inheritDoc}
     */
    public function promptTokens(): int
    {
        return $this->promptTokens;
    }

    /**
     * {@inheritDoc}
     */
    public function completionTokens(): int
    {
        return $this->completionTokens;
    }

    /**
     * {@inheritDoc}
     */
    public function costUsd(): float
    {
        return $this->costUsd;
    }

    /**
     * {@inheritDoc}
     */
    public function durationMs(): int
    {
        return $this->durationMs;
    }

    /**
     * {@inheritDoc}
     */
    public function toolCalls(): array
    {
        return $this->toolCalls;
    }

    /**
     * {@inheritDoc}
     */
    public function structured(): ?array
    {
        return $this->structured;
    }

    /**
     * {@inheritDoc}
     */
    public function provider(): string
    {
        return $this->provider;
    }

    /**
     * {@inheritDoc}
     */
    public function model(): string
    {
        return $this->model;
    }

    /**
     * {@inheritDoc}
     */
    public function metadata(): array
    {
        return $this->metadata;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'provider' => $this->provider,
            'model' => $this->model,
            'prompt_tokens' => $this->promptTokens,
            'completion_tokens' => $this->completionTokens,
            'cost_usd' => $this->costUsd,
            'duration_ms' => $this->durationMs,
            'tool_calls' => $this->toolCalls,
            'structured' => $this->structured,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }
}
