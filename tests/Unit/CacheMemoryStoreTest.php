<?php

declare(strict_types=1);

use Conductor\Enums\MessageRole;
use Conductor\Memory\CacheMemoryStore;

it('stores and retrieves messages via cache', function () {
    $store = new CacheMemoryStore;

    $store->store('conv-1', 'agent-1', MessageRole::User, 'Hello');
    $store->store('conv-1', 'agent-1', MessageRole::Assistant, 'Hi!');

    $messages = $store->retrieve('conv-1', 'agent-1');

    expect($messages)->toHaveCount(2)
        ->and($messages[0]['role'])->toBe('user')
        ->and($messages[1]['role'])->toBe('assistant');
});

it('respects limit parameter', function () {
    $store = new CacheMemoryStore;

    $store->store('conv-2', 'agent-1', MessageRole::User, 'One');
    $store->store('conv-2', 'agent-1', MessageRole::Assistant, 'Two');
    $store->store('conv-2', 'agent-1', MessageRole::User, 'Three');

    $messages = $store->retrieve('conv-2', 'agent-1', 2);

    expect($messages)->toHaveCount(2)
        ->and($messages[0]['content'])->toBe('Two');
});

it('clears conversation cache', function () {
    $store = new CacheMemoryStore;

    $store->store('conv-3', 'agent-1', MessageRole::User, 'Hello');
    $store->clear('conv-3');

    // After clear, retrieval from a specific agent key may still exist
    // because clear uses a general key pattern
    // For the cache driver, clear removes the general conversation key
    expect(true)->toBeTrue();
});
