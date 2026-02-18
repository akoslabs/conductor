<?php

declare(strict_types=1);

namespace Conductor\Contracts;

use Conductor\Exceptions\AgentException;

interface AgentInterface
{
    /**
     * The unique name identifier for this agent.
     */
    public function name(): string;

    /**
     * The system prompt that defines the agent's behavior and role.
     */
    public function systemPrompt(): string;

    /**
     * The tools available to this agent for function calling.
     *
     * @return array<class-string<ToolInterface>>
     */
    public function tools(): array;

    /**
     * The memory driver for conversation persistence, or null for no memory.
     */
    public function memory(): ?string;

    /**
     * Run the agent with the given input and return a response.
     *
     * @param  string  $input  The user message to process.
     * @param  string|null  $conversationId  Optional conversation ID for memory continuity.
     *
     * @throws AgentException
     */
    public static function run(string $input, ?string $conversationId = null): AgentResponseInterface;

    /**
     * Stream the agent's response as it is generated.
     *
     * @param  string  $input  The user message to process.
     * @param  string|null  $conversationId  Optional conversation ID for memory continuity.
     * @return \Generator<int, AgentResponseInterface>
     *
     * @throws AgentException
     */
    public static function stream(string $input, ?string $conversationId = null): \Generator;
}
