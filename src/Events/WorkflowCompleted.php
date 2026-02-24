<?php

declare(strict_types=1);

namespace Conductor\Events;

use Conductor\Contracts\WorkflowResultInterface;
use Illuminate\Foundation\Events\Dispatchable;

final class WorkflowCompleted
{
    use Dispatchable;

    /**
     * @param  string  $workflowName  The workflow name.
     * @param  WorkflowResultInterface  $result  The workflow result.
     */
    public function __construct(
        public readonly string $workflowName,
        public readonly WorkflowResultInterface $result,
    ) {}
}
