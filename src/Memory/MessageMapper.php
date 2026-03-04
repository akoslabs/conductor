<?php

declare(strict_types=1);

namespace Conductor\Memory;

use Prism\Prism\Contracts\Message;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;

final class MessageMapper
{
    /**
     * Convert stored message arrays to Prism message objects.
     *
     * @param  array<int, array{role: string, content: string, metadata: array|null}>  $messages
     * @return array<int, Message>
     */
    public static function toPrismMessages(array $messages): array
    {
        $prismMessages = [];

        foreach ($messages as $message) {
            $prismMessage = match ($message['role']) {
                'user' => new UserMessage($message['content']),
                'assistant' => new AssistantMessage($message['content']),
                default => null,
            };

            if ($prismMessage !== null) {
                $prismMessages[] = $prismMessage;
            }
        }

        return $prismMessages;
    }

    /**
     * Convert a Prism message object to a stored message array.
     *
     * @param  Message  $message  The Prism message.
     * @return array{role: string, content: string, metadata: array|null}
     */
    public static function fromPrismMessage(Message $message): array
    {
        $role = match (true) {
            $message instanceof UserMessage => 'user',
            $message instanceof AssistantMessage => 'assistant',
            default => 'system',
        };

        return [
            'role' => $role,
            'content' => $message->content,
            'metadata' => null,
        ];
    }
}
