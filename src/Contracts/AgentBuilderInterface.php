<?php

declare(strict_types=1);

namespace Conductor\Contracts;

use Conductor\Exceptions\AgentException;

interface AgentBuilderInterface
{
    /**
     * Set the LLM provider and model to use.
     *
     * @param  string  $provider  The Prism provider name (e.g., 'anthropic', 'openai').
     * @param  string  $model  The model identifier (e.g., 'claude-sonnet-4-20250514').
     */
    public function using(string $provider, string $model): static;

    /**
     * Set the system prompt for this agent.
     *
     * @param  string  $prompt  The system prompt text.
     */
    public function withSystemPrompt(string $prompt): static;

    /**
     * Set the tools available to this agent.
     *
     * @param  array<class-string<ToolInterface>>  $tools  Tool class names.
     */
    public function withTools(array $tools): static;

    /**
     * Enable memory for this agent with an optional conversation identifier.
     *
     * @param  string|null  $conversationId  The conversation ID for memory continuity.
     */
    public function withMemory(?string $conversationId = null): static;

    /**
     * Add a fallback provider and model in case the primary fails.
     *
     * @param  string  $provider  The fallback Prism provider name.
     * @param  string  $model  The fallback model identifier.
     */
    public function withFallback(string $provider, string $model): static;

    /**
     * Set a maximum token budget for this agent's execution.
     *
     * @param  int  $maxTokens  The maximum number of tokens allowed.
     */
    public function withTokenBudget(int $maxTokens): static;

    /**
     * Define a schema for structured output from the agent.
     *
     * @param  array<string, string>  $schema  Schema definition with validation rules.
     */
    public function withSchema(array $schema): static;

    /**
     * Set the maximum number of tool-calling steps the agent may take.
     *
     * @param  int  $steps  The maximum number of steps.
     */
    public function withMaxSteps(int $steps): static;

    /**
     * Attach arbitrary metadata to this agent execution.
     *
     * @param  array<string, mixed>  $metadata  Key-value metadata pairs.
     */
    public function withMetadata(array $metadata): static;

    /**
     * Execute the agent with the given input and return a response.
     *
     * @param  string  $input  The user message to process.
     *
     * @throws AgentException
     */
    public function run(string $input): AgentResponseInterface;

    /**
     * Stream the agent's response as it is generated.
     *
     * @param  string  $input  The user message to process.
     * @return \Generator<int, AgentResponseInterface>
     *
     * @throws AgentException
     */
    public function stream(string $input): \Generator;
}
