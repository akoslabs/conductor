<?php

declare(strict_types=1);

namespace Conductor\Workflows\Concerns;

use Closure;
use Conductor\Workflows\WorkflowStep;

trait HasConditions
{
    /**
     * Add a conditional step.
     *
     * @param  string  $name  The step name.
     * @param  Closure  $condition  The condition closure.
     * @param  Closure  $callable  The step callable.
     * @param  array<int, string>  $dependsOn  Step dependencies.
     */
    public function addConditionalStep(string $name, Closure $condition, Closure $callable, array $dependsOn = []): static
    {
        $this->steps[] = new WorkflowStep(
            name: $name,
            callable: $callable,
            dependsOn: $dependsOn,
            condition: $condition,
        );

        return $this;
    }
}
