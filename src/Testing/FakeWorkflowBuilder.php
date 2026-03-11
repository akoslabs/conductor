<?php

declare(strict_types=1);

namespace Conductor\Testing;

use Closure;
use Conductor\Contracts\WorkflowResultInterface;
use Conductor\Workflows\WorkflowResult;

final class FakeWorkflowBuilder
{
    private ?WorkflowResultInterface $fakeResult = null;

    /**
     * @param  string  $name  The workflow name.
     */
    public function __construct(
        private readonly string $name,
    ) {}

    /**
     * Set the fake result to return.
     *
     * @param  WorkflowResultInterface  $result  The fake result.
     */
    public function returns(WorkflowResultInterface $result): static
    {
        $this->fakeResult = $result;

        return $this;
    }

    /**
     * Add a step (no-op in fake).
     */
    public function step(string $name, Closure $callable, array $dependsOn = [], ?int $retries = null, ?int $backoffMs = null): static
    {
        return $this;
    }

    /**
     * Set token budget (no-op in fake).
     */
    public function withTokenBudget(int $maxTokens): static
    {
        return $this;
    }

    /**
     * Set metadata (no-op in fake).
     */
    public function withMetadata(array $metadata): static
    {
        return $this;
    }

    /**
     * Start the workflow.
     *
     * @param  string  $input  The workflow input.
     */
    public function start(string $input): WorkflowResultInterface
    {
        return $this->fakeResult ?? new WorkflowResult(
            finalOutput: '',
            totalTokens: 0,
            totalCostUsd: 0.0,
            stepsCompleted: 0,
        );
    }
}
