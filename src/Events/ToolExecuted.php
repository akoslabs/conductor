<?php

declare(strict_types=1);

namespace Conductor\Events;

use Illuminate\Foundation\Events\Dispatchable;

final class ToolExecuted
{
    use Dispatchable;

    /**
     * @param  string  $toolName  The tool that was executed.
     * @param  array<string, mixed>  $arguments  The arguments passed to the tool.
     * @param  string|array<string, mixed>  $result  The tool result.
     * @param  int  $durationMs  Execution duration in milliseconds.
     * @param  string  $agentName  The agent that invoked the tool.
     */
    public function __construct(
        public readonly string $toolName,
        public readonly array $arguments,
        public readonly string|array $result,
        public readonly int $durationMs,
        public readonly string $agentName,
    ) {}
}
