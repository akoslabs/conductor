<?php

declare(strict_types=1);

namespace Conductor\Agents;

use Conductor\Contracts\AgentResponseInterface;
use Conductor\Monitoring\CostCalculator;
use Prism\Prism\Text\Response as PrismResponse;

final readonly class AgentResponse implements AgentResponseInterface
{
    /**
     * @param  PrismResponse  $prismResponse  The underlying Prism response.
     * @param  string  $provider  The provider that handled this request.
     * @param  string  $model  The model that handled this request.
     * @param  int  $durationMs  The total duration in milliseconds.
     * @param  array<string, mixed>  $metadata  Arbitrary metadata.
     */
    public function __construct(
        private PrismResponse $prismResponse,
        private string $provider,
        private string $model,
        private int $durationMs,
        private array $metadata = [],
    ) {}

    /**
     * {@inheritDoc}
     */
    public function text(): string
    {
        return $this->prismResponse->text;
    }

    /**
     * {@inheritDoc}
     */
    public function promptTokens(): int
    {
        return $this->prismResponse->usage->promptTokens;
    }

    /**
     * {@inheritDoc}
     */
    public function completionTokens(): int
    {
        return $this->prismResponse->usage->completionTokens;
    }

    /**
     * {@inheritDoc}
     */
    public function costUsd(): float
    {
        return CostCalculator::calculate(
            $this->model,
            $this->promptTokens(),
            $this->completionTokens(),
        );
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
        $calls = [];

        foreach ($this->prismResponse->steps as $step) {
            foreach ($step->toolCalls as $toolCall) {
                $matchingResult = null;
                foreach ($step->toolResults as $toolResult) {
                    if ($toolResult->toolCallId === $toolCall->id) {
                        $matchingResult = $toolResult->result;
                        break;
                    }
                }

                $calls[] = [
                    'name' => $toolCall->name,
                    'arguments' => $toolCall->arguments(),
                    'result' => $matchingResult,
                ];
            }
        }

        return $calls;
    }

    /**
     * {@inheritDoc}
     */
    public function structured(): ?array
    {
        return null;
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
            'text' => $this->text(),
            'provider' => $this->provider,
            'model' => $this->model,
            'prompt_tokens' => $this->promptTokens(),
            'completion_tokens' => $this->completionTokens(),
            'cost_usd' => $this->costUsd(),
            'duration_ms' => $this->durationMs,
            'tool_calls' => $this->toolCalls(),
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
