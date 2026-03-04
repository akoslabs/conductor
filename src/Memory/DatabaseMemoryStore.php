<?php

declare(strict_types=1);

namespace Conductor\Memory;

use Conductor\Contracts\MemoryStoreInterface;
use Conductor\Enums\MessageRole;
use Conductor\Models\ConversationMessage;

final class DatabaseMemoryStore implements MemoryStoreInterface
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
        ConversationMessage::create([
            'conversation_id' => $conversationId,
            'agent_name' => $agentName,
            'role' => $role->value,
            'content' => $content,
            'metadata' => $metadata,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function retrieve(string $conversationId, string $agentName, ?int $limit = null): array
    {
        $query = ConversationMessage::where('conversation_id', $conversationId)
            ->where('agent_name', $agentName)
            ->orderBy('created_at', 'asc');

        if ($limit !== null) {
            $query->latest()->limit($limit);

            return $query->get()
                ->sortBy('created_at')
                ->map(fn (ConversationMessage $message): array => [
                    'role' => $message->role,
                    'content' => $message->content,
                    'metadata' => $message->metadata,
                ])
                ->values()
                ->all();
        }

        return $query->get()
            ->map(fn (ConversationMessage $message): array => [
                'role' => $message->role,
                'content' => $message->content,
                'metadata' => $message->metadata,
            ])
            ->all();
    }

    /**
     * {@inheritDoc}
     */
    public function clear(string $conversationId): void
    {
        ConversationMessage::where('conversation_id', $conversationId)->delete();
    }
}
