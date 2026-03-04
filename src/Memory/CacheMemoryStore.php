<?php

declare(strict_types=1);

namespace Conductor\Memory;

use Conductor\Contracts\MemoryStoreInterface;
use Conductor\Enums\MessageRole;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

final class CacheMemoryStore implements MemoryStoreInterface
{
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
        $key = $this->cacheKey($conversationId, $agentName);
        $cache = $this->cache();
        $ttl = config('conductor.memory.cache.ttl', 3600);

        /** @var array<int, array{role: string, content: string, metadata: array|null}> $messages */
        $messages = $cache->get($key, []);

        $messages[] = [
            'role' => $role->value,
            'content' => $content,
            'metadata' => $metadata,
        ];

        $cache->put($key, $messages, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function retrieve(string $conversationId, string $agentName, ?int $limit = null): array
    {
        $key = $this->cacheKey($conversationId, $agentName);

        /** @var array<int, array{role: string, content: string, metadata: array|null}> $messages */
        $messages = $this->cache()->get($key, []);

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
        $this->cache()->forget("conductor:memory:{$conversationId}");
    }

    /**
     * Get the cache key for a conversation and agent.
     *
     * @param  string  $conversationId  The conversation identifier.
     * @param  string  $agentName  The agent name.
     */
    private function cacheKey(string $conversationId, string $agentName): string
    {
        return "conductor:memory:{$conversationId}:{$agentName}";
    }

    /**
     * Get the cache repository.
     */
    private function cache(): Repository
    {
        $store = config('conductor.memory.cache.store');

        return $store ? Cache::store($store) : Cache::store();
    }
}
