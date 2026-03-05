<?php

declare(strict_types=1);

use Conductor\Enums\MessageRole;
use Conductor\Memory\ArrayMemoryStore;

it('stores and retrieves messages', function () {
    $store = new ArrayMemoryStore;

    $store->store('conv-1', 'agent-1', MessageRole::User, 'Hello');
    $store->store('conv-1', 'agent-1', MessageRole::Assistant, 'Hi there!');

    $messages = $store->retrieve('conv-1', 'agent-1');

    expect($messages)->toHaveCount(2)
        ->and($messages[0]['role'])->toBe('user')
        ->and($messages[0]['content'])->toBe('Hello')
        ->and($messages[1]['role'])->toBe('assistant')
        ->and($messages[1]['content'])->toBe('Hi there!');
});

it('respects limit parameter', function () {
    $store = new ArrayMemoryStore;

    $store->store('conv-1', 'agent-1', MessageRole::User, 'Message 1');
    $store->store('conv-1', 'agent-1', MessageRole::Assistant, 'Message 2');
    $store->store('conv-1', 'agent-1', MessageRole::User, 'Message 3');

    $messages = $store->retrieve('conv-1', 'agent-1', 2);

    expect($messages)->toHaveCount(2)
        ->and($messages[0]['content'])->toBe('Message 2')
        ->and($messages[1]['content'])->toBe('Message 3');
});

it('clears messages for a conversation', function () {
    $store = new ArrayMemoryStore;

    $store->store('conv-1', 'agent-1', MessageRole::User, 'Hello');
    $store->store('conv-1', 'agent-2', MessageRole::User, 'World');
    $store->store('conv-2', 'agent-1', MessageRole::User, 'Other');

    $store->clear('conv-1');

    expect($store->retrieve('conv-1', 'agent-1'))->toBeEmpty()
        ->and($store->retrieve('conv-1', 'agent-2'))->toBeEmpty()
        ->and($store->retrieve('conv-2', 'agent-1'))->toHaveCount(1);
});

it('separates messages by agent name', function () {
    $store = new ArrayMemoryStore;

    $store->store('conv-1', 'agent-1', MessageRole::User, 'For agent 1');
    $store->store('conv-1', 'agent-2', MessageRole::User, 'For agent 2');

    expect($store->retrieve('conv-1', 'agent-1'))->toHaveCount(1)
        ->and($store->retrieve('conv-1', 'agent-2'))->toHaveCount(1);
});
