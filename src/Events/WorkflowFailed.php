<?php

declare(strict_types=1);

namespace Conductor\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Throwable;

final class WorkflowFailed
{
    use Dispatchable;

    /**
     * @param  string  $workflowName  The workflow name.
     * @param  Throwable  $exception  The exception that caused the failure.
     */
    public function __construct(
        public readonly string $workflowName,
        public readonly Throwable $exception,
    ) {}
}
