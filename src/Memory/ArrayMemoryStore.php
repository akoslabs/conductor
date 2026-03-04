<?php

declare(strict_types=1);

namespace Conductor\Memory;

use Conductor\Contracts\MemoryStoreInterface;
use Conductor\Enums\MessageRole;

final class ArrayMemoryStore implements MemoryStoreInterface
{
    /** @var array<string, array<int, array{role: string, content: string, metadata: array|null}>> */
    private array $messages = [];

    /**
     * {@inheritDoc}
     */
    public function store(
        string $conversationId,
        string $agentName,
        MessageRole $role,
        string $content,
        ?array $metadata = null,
    ): void {
        $key = "{$conversationId}:{$agentName}";

        $this->messages[$key][] = [
            'role' => $role->value,
            'content' => $content,
            'metadata' => $metadata,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function retrieve(string $conversationId, string $agentName, ?int $limit = null): array
    {
        $key = "{$conversationId}:{$agentName}";
        $messages = $this->messages[$key] ?? [];

        if ($limit !== null) {
            $messages = array_slice($messages, -$limit);
        }

        return $messages;
    }

    /**
     * {@inheritDoc}
     */
    public function clear(string $conversationId): void
    {
        $keysToRemove = [];

        foreach (array_keys($this->messages) as $key) {
            if (str_starts_with($key, "{$conversationId}:")) {
                $keysToRemove[] = $key;
            }
        }

        foreach ($keysToRemove as $key) {
            unset($this->messages[$key]);
        }
    }
}
