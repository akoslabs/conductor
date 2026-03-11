<?php

declare(strict_types=1);

namespace Conductor\Testing;

use Conductor\Contracts\AgentResponseInterface;
use Conductor\Contracts\WorkflowResultInterface;

final readonly class FakeWorkflowResult implements WorkflowResultInterface
{
    /**
     * @param  string  $outputText  The output text.
     * @param  int  $totalTokens  Total tokens consumed.
     * @param  float  $totalCostUsd  Total cost.
     * @param  int  $stepsCompleted  Number of steps completed.
     */
    public function __construct(
        private string $outputText = '',
        private int $totalTokens = 0,
        private float $totalCostUsd = 0.0,
        private int $stepsCompleted = 0,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function output(): AgentResponseInterface
    {
        return new FakeAgentResponse(text: $this->outputText);
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
     * {@inheritDoc}
     */
    public function toArray(): array
    {
        return [
            'output' => $this->outputText,
            'total_tokens' => $this->totalTokens,
            'total_cost_usd' => $this->totalCostUsd,
            'steps_completed' => $this->stepsCompleted,
        ];
    }
}
