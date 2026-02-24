<?php

declare(strict_types=1);

namespace Conductor\Events;

use Illuminate\Foundation\Events\Dispatchable;

final class WorkflowStepCompleted
{
    use Dispatchable;

    /**
     * @param  string  $workflowName  The workflow name.
     * @param  string  $stepName  The step that completed.
     * @param  mixed  $output  The step output.
     */
    public function __construct(
        public readonly string $workflowName,
        public readonly string $stepName,
        public readonly mixed $output,
    ) {}
}
