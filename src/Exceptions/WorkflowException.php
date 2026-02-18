<?php

declare(strict_types=1);

namespace Conductor\Exceptions;

use RuntimeException;
use Throwable;

final class WorkflowException extends RuntimeException
{
    /**
     * Create a new workflow execution failure exception.
     *
     * @param  string  $workflowName  The name of the workflow that failed.
     * @param  string  $message  The failure message.
     * @param  Throwable|null  $previous  The previous exception.
     */
    public function __construct(
        public readonly string $workflowName,
        string $message = '',
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            "Workflow [{$workflowName}] failed: {$message}",
            previous: $previous,
        );
    }
}
