<?php

declare(strict_types=1);

namespace Conductor\Workflows\Concerns;

use Closure;
use Conductor\Workflows\WorkflowStep;

trait HasSteps
{
    /** @var array<int, WorkflowStep> */
    protected array $steps = [];

    /**
     * Add a step to the workflow.
     *
     * @param  string  $name  The step name.
     * @param  Closure  $callable  The step callable.
     * @param  array<int, string>  $dependsOn  Step dependencies.
     */
    public function addStep(string $name, Closure $callable, array $dependsOn = []): static
    {
        $this->steps[] = new WorkflowStep(
            name: $name,
            callable: $callable,
            dependsOn: $dependsOn,
        );

        return $this;
    }

    /**
     * Get all steps.
     *
     * @return array<int, WorkflowStep>
     */
    public function getSteps(): array
    {
        return $this->steps;
    }
}
