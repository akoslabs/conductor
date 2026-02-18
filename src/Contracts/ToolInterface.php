<?php

declare(strict_types=1);

namespace Conductor\Contracts;

interface ToolInterface
{
    /**
     * The tool name used in LLM function calling.
     */
    public function name(): string;

    /**
     * Human-readable description of the tool for the LLM.
     */
    public function description(): string;

    /**
     * JSON Schema definition for the tool's parameters.
     *
     * @return array<string, mixed>
     */
    public function parameters(): array;

    /**
     * Execute the tool with the given arguments.
     *
     * @param  array<string, mixed>  $arguments  Validated arguments from the LLM.
     * @return string|array<string, mixed> The result to send back to the LLM.
     */
    public function execute(array $arguments): string|array;
}
