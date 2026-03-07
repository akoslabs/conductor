<?php

declare(strict_types=1);

use Conductor\Rag\EmbeddingGenerator;
use Prism\Prism\Prism;
use Prism\Prism\Testing\EmbeddingsResponseFake;
use Prism\Prism\ValueObjects\Embedding;

it('generates embeddings via Prism', function () {
    Prism::fake([
        EmbeddingsResponseFake::make()
            ->withEmbeddings([Embedding::fromArray([0.1, 0.2, 0.3])]),
    ]);

    $embedding = EmbeddingGenerator::generate('Test text', 'openai', 'text-embedding-3-small');

    expect($embedding)->toBe([0.1, 0.2, 0.3]);
});
