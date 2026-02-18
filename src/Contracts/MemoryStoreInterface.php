<?php

declare(strict_types=1);

namespace Conductor\Contracts;

use Conductor\Enums\MessageRole;

interface MemoryStoreInterface
{
    /**
     * Store a message in conversation history.
     *
     * @param  string  $conversationId  The conversation identifier.
     * @param  string  $agentName  The agent this message belongs to.
     * @param  MessageRole  $role  The role of the message sender.
     * @param  string  $content  The message content.
     * @param  array<string, mixed>|null  $metadata  Optional metadata for the message.
     */
    public function store(
        string $conversationId,
        string $agentName,
        MessageRole $role,
        string $content,
        ?array $metadata = null,
    ): void;

    /**
     * Retrieve conversation history for a given conversation and agent.
     *
     * @param  string  $conversationId  The conversation identifier.
     * @param  string  $agentName  The agent name to filter by.
     * @param  int|null  $limit  Maximum number of messages to retrieve.
     * @return array<int, array{role: string, content: string, metadata: array|null}>
     */
    public function retrieve(string $conversationId, string $agentName, ?int $limit = null): array;

    /**
     * Clear all messages for a given conversation.
     *
     * @param  string  $conversationId  The conversation identifier.
     */
    public function clear(string $conversationId): void;
}
