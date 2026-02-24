<?php

declare(strict_types=1);

namespace Conductor\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Throwable;

final class AgentFailed
{
    use Dispatchable;

    /**
     * @param  string  $agentName  The agent name identifier.
     * @param  Throwable  $exception  The exception that caused the failure.
     * @param  string  $input  The user input that was being processed.
     */
    public function __construct(
        public readonly string $agentName,
        public readonly Throwable $exception,
        public readonly string $input,
    ) {}
}
