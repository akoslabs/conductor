<?php

declare(strict_types=1);

use Conductor\Rag\VectorStores\InMemoryVectorStore;

it('stores and queries documents', function () {
    $store = new InMemoryVectorStore;

    $store->store('doc-1', [1.0, 0.0, 0.0], 'About cats', ['topic' => 'animals']);
    $store->store('doc-2', [0.0, 1.0, 0.0], 'About dogs', ['topic' => 'animals']);
    $store->store('doc-3', [0.9, 0.1, 0.0], 'About kittens', ['topic' => 'animals']);

    $results = $store->query([1.0, 0.0, 0.0], 2);

    expect($results)->toHaveCount(2)
        ->and($results[0]['id'])->toBe('doc-1') // exact match
        ->and($results[0]['score'])->toBe(1.0)
        ->and($results[1]['id'])->toBe('doc-3'); // similar
});

it('filters by metadata', function () {
    $store = new InMemoryVectorStore;

    $store->store('doc-1', [1.0, 0.0], 'Cat content', ['type' => 'article']);
    $store->store('doc-2', [0.9, 0.1], 'Dog content', ['type' => 'faq']);

    $results = $store->query([1.0, 0.0], 10, ['type' => 'faq']);

    expect($results)->toHaveCount(1)
        ->and($results[0]['id'])->toBe('doc-2');
});

it('deletes documents', function () {
    $store = new InMemoryVectorStore;

    $store->store('doc-1', [1.0, 0.0], 'Content');
    $store->delete('doc-1');

    $results = $store->query([1.0, 0.0]);

    expect($results)->toBeEmpty();
});

it('handles empty store', function () {
    $store = new InMemoryVectorStore;

    $results = $store->query([1.0, 0.0]);

    expect($results)->toBe([]);
});
