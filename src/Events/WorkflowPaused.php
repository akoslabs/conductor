<?php

declare(strict_types=1);

namespace Conductor\Events;

use Illuminate\Foundation\Events\Dispatchable;

final class WorkflowPaused
{
    use Dispatchable;

    /**
     * @param  string  $workflowName  The workflow name.
     * @param  string  $stepName  The step that triggered the pause.
     * @param  string  $runId  The workflow run UUID.
     */
    public function __construct(
        public readonly string $workflowName,
        public readonly string $stepName,
        public readonly string $runId,
    ) {}
}
