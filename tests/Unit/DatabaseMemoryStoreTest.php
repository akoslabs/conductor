<?php

declare(strict_types=1);

use Conductor\Enums\MessageRole;
use Conductor\Memory\DatabaseMemoryStore;

beforeEach(function () {
    $this->artisan('migrate')->assertSuccessful();
});

it('stores and retrieves messages from database', function () {
    $store = new DatabaseMemoryStore;

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
    $store = new DatabaseMemoryStore;

    $store->store('conv-1', 'agent-1', MessageRole::User, 'One');
    $store->store('conv-1', 'agent-1', MessageRole::Assistant, 'Two');
    $store->store('conv-1', 'agent-1', MessageRole::User, 'Three');

    $messages = $store->retrieve('conv-1', 'agent-1', 2);

    expect($messages)->toHaveCount(2);
});

it('clears messages from database', function () {
    $store = new DatabaseMemoryStore;

    $store->store('conv-1', 'agent-1', MessageRole::User, 'Hello');
    $store->clear('conv-1');

    expect($store->retrieve('conv-1', 'agent-1'))->toBeEmpty();
});
