<?php

declare(strict_types=1);

namespace Conductor\Workflows;

use Closure;
use Conductor\Contracts\WorkflowResultInterface;

final class WorkflowBuilder
{
    /** @var array<int, WorkflowStep> */
    private array $steps = [];

    private ?int $tokenBudget = null;

    /** @var array<string, mixed> */
    private array $metadata = [];

    /**
     * @param  string  $name  The workflow name.
     */
    public function __construct(
        private readonly string $name,
    ) {}

    /**
     * Add a step to the workflow.
     *
     * @param  string  $name  The step name.
     * @param  Closure  $callable  The step callable.
     * @param  array<int, string>  $dependsOn  Step dependencies.
     * @param  int|null  $retries  Maximum retries (null = use config default).
     * @param  int|null  $backoffMs  Backoff in ms (null = use config default).
     */
    public function step(
        string $name,
        Closure $callable,
        array $dependsOn = [],
        ?int $retries = null,
        ?int $backoffMs = null,
    ): static {
        $this->steps[] = new WorkflowStep(
            name: $name,
            callable: $callable,
            dependsOn: $dependsOn,
            retries: $retries ?? (int) config('conductor.workflows.default_retry_attempts', 3),
            backoffMs: $backoffMs ?? (int) config('conductor.workflows.default_retry_backoff_ms', 1000),
        );

        return $this;
    }

    /**
     * Add a conditional step.
     *
     * @param  string  $name  The step name.
     * @param  Closure  $condition  Condition receiving WorkflowState, returns bool.
     * @param  Closure  $callable  The step callable.
     * @param  array<int, string>  $dependsOn  Step dependencies.
     */
    public function when(string $name, Closure $condition, Closure $callable, array $dependsOn = []): static
    {
        $this->steps[] = new WorkflowStep(
            name: $name,
            callable: $callable,
            dependsOn: $dependsOn,
            retries: (int) config('conductor.workflows.default_retry_attempts', 3),
            backoffMs: (int) config('conductor.workflows.default_retry_backoff_ms', 1000),
            condition: $condition,
        );

        return $this;
    }

    /**
     * Add a step that requires human approval.
     *
     * @param  string  $name  The step name.
     * @param  Closure  $callable  The step callable.
     * @param  array<int, string>  $dependsOn  Step dependencies.
     */
    public function humanApproval(string $name, Closure $callable, array $dependsOn = []): static
    {
        $this->steps[] = new WorkflowStep(
            name: $name,
            callable: $callable,
            dependsOn: $dependsOn,
            retries: (int) config('conductor.workflows.default_retry_attempts', 3),
            backoffMs: (int) config('conductor.workflows.default_retry_backoff_ms', 1000),
            requiresHumanApproval: true,
        );

        return $this;
    }

    /**
     * Declare parallel steps (executes sequentially in MVP).
     *
     * @param  array<string, Closure>  $steps  Named steps to run.
     * @param  array<int, string>  $dependsOn  Dependencies for all parallel steps.
     */
    public function parallel(array $steps, array $dependsOn = []): static
    {
        foreach ($steps as $name => $callable) {
            $this->step($name, $callable, $dependsOn);
        }

        return $this;
    }

    /**
     * Set a token budget for the workflow.
     *
     * @param  int  $maxTokens  The maximum token budget.
     */
    public function withTokenBudget(int $maxTokens): static
    {
        $this->tokenBudget = $maxTokens;

        return $this;
    }

    /**
     * Attach metadata to the workflow.
     *
     * @param  array<string, mixed>  $metadata  Key-value metadata pairs.
     */
    public function withMetadata(array $metadata): static
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * Start the workflow.
     *
     * @param  string  $input  The workflow input.
     */
    public function start(string $input): WorkflowResultInterface
    {
        $engine = new WorkflowEngine;

        return $engine->execute(
            workflowName: $this->name,
            steps: $this->steps,
            input: $input,
            metadata: $this->metadata,
            tokenBudget: $this->tokenBudget,
        );
    }

    /**
     * Resume a paused workflow.
     *
     * @param  string  $runId  The workflow run UUID.
     * @param  array<string, mixed>  $data  Data to provide to the paused step.
     */
    public function resume(string $runId, array $data = []): WorkflowResultInterface
    {
        $engine = new WorkflowEngine;

        return $engine->resume(
            runId: $runId,
            data: $data,
            steps: $this->steps,
            tokenBudget: $this->tokenBudget,
        );
    }
}
