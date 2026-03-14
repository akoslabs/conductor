<?php

declare(strict_types=1);

use Conductor\Rag\RagPipeline;
use Conductor\Rag\VectorStores\InMemoryVectorStore;
use Prism\Prism\Prism;
use Prism\Prism\Testing\EmbeddingsResponseFake;
use Prism\Prism\ValueObjects\Embedding;

it('ingests a document and queries it', function () {
    // Fake multiple embedding calls (one per chunk + one for query)
    Prism::fake([
        EmbeddingsResponseFake::make()
            ->withEmbeddings([Embedding::fromArray([1.0, 0.0, 0.0])]),
        EmbeddingsResponseFake::make()
            ->withEmbeddings([Embedding::fromArray([0.9, 0.1, 0.0])]),
        EmbeddingsResponseFake::make()
            ->withEmbeddings([Embedding::fromArray([1.0, 0.0, 0.0])]),
    ]);

    $store = new InMemoryVectorStore;
    $pipeline = new RagPipeline($store);
    $pipeline->using('openai', 'text-embedding-3-small')
        ->withChunking(100, 20);

    $chunksStored = $pipeline->ingest(
        'First sentence about cats. Second sentence about dogs.',
        'doc-1',
        ['source' => 'test'],
    );

    expect($chunksStored)->toBeGreaterThanOrEqual(1);

    $results = $pipeline->query('Tell me about cats');

    expect($results)->not->toBeEmpty()
        ->and($results[0])->toHaveKeys(['id', 'content', 'score', 'metadata']);
});
