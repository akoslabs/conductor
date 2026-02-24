<?php

declare(strict_types=1);

namespace Conductor\Events;

use Illuminate\Foundation\Events\Dispatchable;

final class WorkflowStarted
{
    use Dispatchable;

    /**
     * @param  string  $workflowName  The workflow name.
     * @param  string  $input  The workflow input.
     */
    public function __construct(
        public readonly string $workflowName,
        public readonly string $input,
    ) {}
}
