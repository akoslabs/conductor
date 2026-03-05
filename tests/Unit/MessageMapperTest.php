<?php

declare(strict_types=1);

use Conductor\Memory\MessageMapper;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;

it('converts stored messages to Prism messages', function () {
    $messages = [
        ['role' => 'user', 'content' => 'Hello', 'metadata' => null],
        ['role' => 'assistant', 'content' => 'Hi!', 'metadata' => null],
    ];

    $prismMessages = MessageMapper::toPrismMessages($messages);

    expect($prismMessages)->toHaveCount(2)
        ->and($prismMessages[0])->toBeInstanceOf(UserMessage::class)
        ->and($prismMessages[0]->content)->toBe('Hello')
        ->and($prismMessages[1])->toBeInstanceOf(AssistantMessage::class)
        ->and($prismMessages[1]->content)->toBe('Hi!');
});

it('skips unknown roles', function () {
    $messages = [
        ['role' => 'system', 'content' => 'System message', 'metadata' => null],
        ['role' => 'user', 'content' => 'User message', 'metadata' => null],
    ];

    $prismMessages = MessageMapper::toPrismMessages($messages);

    expect($prismMessages)->toHaveCount(1)
        ->and($prismMessages[0])->toBeInstanceOf(UserMessage::class);
});

it('converts Prism messages back to stored format', function () {
    $userMessage = new UserMessage('Hello');
    $stored = MessageMapper::fromPrismMessage($userMessage);

    expect($stored['role'])->toBe('user')
        ->and($stored['content'])->toBe('Hello')
        ->and($stored['metadata'])->toBeNull();

    $assistantMessage = new AssistantMessage('Hi!');
    $stored = MessageMapper::fromPrismMessage($assistantMessage);

    expect($stored['role'])->toBe('assistant')
        ->and($stored['content'])->toBe('Hi!');
});
