<?php

declare(strict_types=1);

namespace Conductor\Events;

use Illuminate\Foundation\Events\Dispatchable;

final class AgentStarted
{
    use Dispatchable;

    /**
     * @param  string  $agentName  The agent name identifier.
     * @param  string  $input  The user input.
     * @param  array<string, mixed>  $metadata  Additional metadata.
     */
    public function __construct(
        public readonly string $agentName,
        public readonly string $input,
        public readonly array $metadata = [],
    ) {}
}
