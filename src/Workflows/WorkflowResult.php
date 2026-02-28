<?php

declare(strict_types=1);

namespace Conductor\Workflows;

use Conductor\Contracts\AgentResponseInterface;
use Conductor\Contracts\WorkflowResultInterface;
use Conductor\Testing\FakeAgentResponse;

final readonly class WorkflowResult implements WorkflowResultInterface
{
    /**
     * @param  mixed  $finalOutput  The final output value.
     * @param  int  $totalTokens  Total tokens consumed.
     * @param  float  $totalCostUsd  Total cost in USD.
     * @param  int  $stepsCompleted  Number of steps completed.
     * @param  string  $status  The workflow status.
     * @param  string|null  $runId  The workflow run UUID.
     */
    public function __construct(
        private mixed $finalOutput,
        private int $totalTokens,
        private float $totalCostUsd,
        private int $stepsCompleted,
        private string $status = 'completed',
        private ?string $runId = null,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function output(): AgentResponseInterface
    {
        if ($this->finalOutput instanceof AgentResponseInterface) {
            return $this->finalOutput;
        }

        $text = is_string($this->finalOutput)
            ? $this->finalOutput
            : json_encode($this->finalOutput, JSON_THROW_ON_ERROR);

        return new FakeAgentResponse(
            text: $text,
            promptTokens: $this->totalTokens > 0 ? (int) ($this->totalTokens * 0.6) : 0,
            completionTokens: $this->totalTokens > 0 ? (int) ($this->totalTokens * 0.4) : 0,
            costUsd: $this->totalCostUsd,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function totalTokens(): int
    {
        return $this->totalTokens;
    }

    /**
     * {@inheritDoc}
     */
    public function totalCostUsd(): float
    {
        return $this->totalCostUsd;
    }

    /**
     * {@inheritDoc}
     */
    public function stepsCompleted(): int
    {
        return $this->stepsCompleted;
    }

    /**
     * Get the workflow status.
     */
    public function status(): string
    {
        return $this->status;
    }

    /**
     * Get the workflow run UUID.
     */
    public function runId(): ?string
    {
        return $this->runId;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'run_id' => $this->runId,
            'total_tokens' => $this->totalTokens,
            'total_cost_usd' => $this->totalCostUsd,
            'steps_completed' => $this->stepsCompleted,
        ];
    }
}
