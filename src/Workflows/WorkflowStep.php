<?php

declare(strict_types=1);

namespace Conductor\Workflows;

use Closure;

final class WorkflowStep
{
    /**
     * @param  string  $name  The step name.
     * @param  Closure  $callable  The step callable receiving WorkflowState.
     * @param  array<int, string>  $dependsOn  Step names this step depends on.
     * @param  int  $retries  Maximum retry attempts.
     * @param  int  $backoffMs  Backoff time between retries in milliseconds.
     * @param  bool  $requiresHumanApproval  Whether to pause for human approval after this step.
     * @param  Closure|null  $condition  Condition closure that receives WorkflowState, returns bool.
     */
    public function __construct(
        public readonly string $name,
        public readonly Closure $callable,
        public readonly array $dependsOn = [],
        public readonly int $retries = 0,
        public readonly int $backoffMs = 1000,
        public readonly bool $requiresHumanApproval = false,
        public readonly ?Closure $condition = null,
    ) {}
}
