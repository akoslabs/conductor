<?php

declare(strict_types=1);

namespace Conductor\Agents\Concerns;

use Conductor\Contracts\MemoryStoreInterface;
use Conductor\Enums\MessageRole;

trait HasMemory
{
    /**
     * Retrieve conversation history from the memory store.
     *
     * @param  string  $conversationId  The conversation identifier.
     * @param  string  $agentName  The agent name.
     * @param  int|null  $limit  Maximum messages to retrieve.
     * @return array<int, array{role: string, content: string, metadata: array|null}>
     */
    protected function retrieveMemory(string $conversationId, string $agentName, ?int $limit = null): array
    {
        if (! app()->bound(MemoryStoreInterface::class)) {
            return [];
        }

        return app(MemoryStoreInterface::class)->retrieve($conversationId, $agentName, $limit);
    }

    /**
     * Store a message in the memory store.
     *
     * @param  string  $conversationId  The conversation identifier.
     * @param  string  $agentName  The agent name.
     * @param  MessageRole  $role  The message role.
     * @param  string  $content  The message content.
     */
    protected function storeMemory(string $conversationId, string $agentName, MessageRole $role, string $content): void
    {
        if (! app()->bound(MemoryStoreInterface::class)) {
            return;
        }

        app(MemoryStoreInterface::class)->store($conversationId, $agentName, $role, $content);
    }
}
