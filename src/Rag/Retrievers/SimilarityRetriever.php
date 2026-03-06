<?php

declare(strict_types=1);

namespace Conductor\Rag\Retrievers;

use Conductor\Contracts\RetrieverInterface;
use Conductor\Contracts\VectorStoreInterface;
use Conductor\Rag\EmbeddingGenerator;

final class SimilarityRetriever implements RetrieverInterface
{
    /**
     * @param  VectorStoreInterface  $vectorStore  The vector store to search.
     * @param  string|null  $embeddingProvider  The embedding provider.
     * @param  string|null  $embeddingModel  The embedding model.
     */
    public function __construct(
        private readonly VectorStoreInterface $vectorStore,
        private readonly ?string $embeddingProvider = null,
        private readonly ?string $embeddingModel = null,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function retrieve(string $query, int $limit = 5): array
    {
        $queryEmbedding = EmbeddingGenerator::generate(
            $query,
            $this->embeddingProvider,
            $this->embeddingModel,
        );

        return $this->vectorStore->query($queryEmbedding, $limit);
    }
}
