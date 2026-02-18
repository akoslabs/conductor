<?php

declare(strict_types=1);

namespace Conductor\Contracts;

interface WorkflowResultInterface
{
    /**
     * The final output of the workflow.
     */
    public function output(): AgentResponseInterface;

    /**
     * The total number of tokens consumed across all workflow steps.
     */
    public function totalTokens(): int;

    /**
     * The total estimated cost in USD across all workflow steps.
     */
    public function totalCostUsd(): float;

    /**
     * The number of steps that completed successfully.
     */
    public function stepsCompleted(): int;

    /**
     * Convert the result to an array representation.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
