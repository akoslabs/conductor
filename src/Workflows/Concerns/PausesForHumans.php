<?php

declare(strict_types=1);

namespace Conductor\Workflows\Concerns;

use Closure;
use Conductor\Workflows\WorkflowStep;

trait PausesForHumans
{
    /**
     * Add a step that requires human approval.
     *
     * @param  string  $name  The step name.
     * @param  Closure  $callable  The step callable.
     * @param  array<int, string>  $dependsOn  Step dependencies.
     */
    public function addHumanApprovalStep(string $name, Closure $callable, array $dependsOn = []): static
    {
        $this->steps[] = new WorkflowStep(
            name: $name,
            callable: $callable,
            dependsOn: $dependsOn,
            requiresHumanApproval: true,
        );

        return $this;
    }
}
