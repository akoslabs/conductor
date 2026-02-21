<?php

declare(strict_types=1);

namespace Conductor\Tools;

final readonly class ToolResult
{
    /**
     * @param  string  $name  The tool name.
     * @param  array<string, mixed>  $arguments  The arguments passed to the tool.
     * @param  string|array<string, mixed>  $result  The tool execution result.
     * @param  int  $durationMs  Execution duration in milliseconds.
     */
    public function __construct(
        public string $name,
        public array $arguments,
        public string|array $result,
        public int $durationMs,
    ) {}

    /**
     * Convert to array representation.
     *
     * @return array{name: string, arguments: array<string, mixed>, result: string|array<string, mixed>, duration_ms: int}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'arguments' => $this->arguments,
            'result' => $this->result,
            'duration_ms' => $this->durationMs,
        ];
    }
}
