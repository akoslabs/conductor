<?php

declare(strict_types=1);

namespace Conductor\Exceptions;

use RuntimeException;
use Throwable;

final class AgentException extends RuntimeException
{
    /**
     * Create a new agent execution failure exception.
     *
     * @param  string  $agentName  The name of the agent that failed.
     * @param  string  $message  The failure message.
     * @param  Throwable|null  $previous  The previous exception.
     */
    public function __construct(
        public readonly string $agentName,
        string $message = '',
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            "Agent [{$agentName}] failed: {$message}",
            previous: $previous,
        );
    }
}
