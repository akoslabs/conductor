<?php

declare(strict_types=1);

namespace Conductor\Contracts;

interface AgentResponseInterface
{
    /**
     * The text content of the agent's response.
     */
    public function text(): string;

    /**
     * The number of prompt tokens consumed.
     */
    public function promptTokens(): int;

    /**
     * The number of completion tokens generated.
     */
    public function completionTokens(): int;

    /**
     * The estimated cost in USD for this interaction.
     */
    public function costUsd(): float;

    /**
     * The total duration of the agent execution in milliseconds.
     */
    public function durationMs(): int;

    /**
     * The tool calls that were invoked during execution.
     *
     * @return array<int, array{name: string, arguments: array, result: mixed}>
     */
    public function toolCalls(): array;

    /**
     * The parsed structured output if a schema was provided, or null otherwise.
     *
     * @return array<string, mixed>|null
     */
    public function structured(): ?array;

    /**
     * The provider that handled this request.
     */
    public function provider(): string;

    /**
     * The model that handled this request.
     */
    public function model(): string;

    /**
     * Arbitrary metadata attached to this response.
     *
     * @return array<string, mixed>
     */
    public function metadata(): array;

    /**
     * Convert the response to an array representation.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;

    /**
     * Convert the response to a JSON string.
     */
    public function toJson(): string;
}
