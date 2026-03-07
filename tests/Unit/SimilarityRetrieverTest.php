<?php

declare(strict_types=1);

use Conductor\Rag\Retrievers\SimilarityRetriever;
use Conductor\Rag\VectorStores\InMemoryVectorStore;
use Prism\Prism\Prism;
use Prism\Prism\Testing\EmbeddingsResponseFake;
use Prism\Prism\ValueObjects\Embedding;

it('retrieves similar documents using embeddings', function () {
    Prism::fake([
        EmbeddingsResponseFake::make()
            ->withEmbeddings([Embedding::fromArray([1.0, 0.0, 0.0])]),
    ]);

    $store = new InMemoryVectorStore;
    $store->store('doc-1', [1.0, 0.0, 0.0], 'Relevant content');
    $store->store('doc-2', [0.0, 1.0, 0.0], 'Irrelevant content');

    $retriever = new SimilarityRetriever($store);
    $results = $retriever->retrieve('query', 1);

    expect($results)->toHaveCount(1)
        ->and($results[0]['content'])->toBe('Relevant content');
});
