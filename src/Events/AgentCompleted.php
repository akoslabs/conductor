<?php

declare(strict_types=1);

namespace Conductor\Events;

use Conductor\Contracts\AgentResponseInterface;
use Illuminate\Foundation\Events\Dispatchable;

final class AgentCompleted
{
    use Dispatchable;

    /**
     * @param  string  $agentName  The agent name identifier.
     * @param  AgentResponseInterface  $response  The agent response.
     * @param  int  $durationMs  Execution duration in milliseconds.
     */
    public function __construct(
        public readonly string $agentName,
        public readonly AgentResponseInterface $response,
        public readonly int $durationMs,
    ) {}
}
