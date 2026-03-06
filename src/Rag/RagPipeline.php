<?php

declare(strict_types=1);

namespace Conductor\Rag;

use Conductor\Contracts\VectorStoreInterface;

final class RagPipeline
{
    private ?string $embeddingProvider = null;

    private ?string $embeddingModel = null;

    private int $chunkSize;

    private int $chunkOverlap;

    /**
     * @param  VectorStoreInterface  $vectorStore  The vector store for embeddings.
     */
    public function __construct(
        private readonly VectorStoreInterface $vectorStore,
    ) {
        $this->chunkSize = (int) config('conductor.rag.chunk_size', 500);
        $this->chunkOverlap = (int) config('conductor.rag.chunk_overlap', 50);
    }

    /**
     * Configure the embedding provider and model.
     *
     * @param  string  $provider  The provider name.
     * @param  string  $model  The model name.
     */
    public function using(string $provider, string $model): static
    {
        $this->embeddingProvider = $provider;
        $this->embeddingModel = $model;

        return $this;
    }

    /**
     * Configure chunk size and overlap.
     *
     * @param  int  $size  Maximum characters per chunk.
     * @param  int  $overlap  Overlap between chunks.
     */
    public function withChunking(int $size, int $overlap = 50): static
    {
        $this->chunkSize = $size;
        $this->chunkOverlap = $overlap;

        return $this;
    }

    /**
     * Ingest a document: load, chunk, embed, and store.
     *
     * @param  string  $content  The document content.
     * @param  string  $documentId  A base identifier for the document.
     * @param  array<string, mixed>  $metadata  Additional metadata.
     * @return int The number of chunks stored.
     */
    public function ingest(string $content, string $documentId, array $metadata = []): int
    {
        $chunks = Chunker::chunk($content, $this->chunkSize, $this->chunkOverlap);

        foreach ($chunks as $index => $chunk) {
            $embedding = EmbeddingGenerator::generate(
                $chunk,
                $this->embeddingProvider,
                $this->embeddingModel,
            );

            $chunkId = "{$documentId}_chunk_{$index}";

            $this->vectorStore->store(
                $chunkId,
                $embedding,
                $chunk,
                array_merge($metadata, [
                    'document_id' => $documentId,
                    'chunk_index' => $index,
                ]),
            );
        }

        return count($chunks);
    }

    /**
     * Query the vector store for relevant chunks.
     *
     * @param  string  $query  The search query.
     * @param  int  $limit  Maximum results.
     * @return array<int, array{id: string, content: string, score: float, metadata: array}>
     */
    public function query(string $query, int $limit = 5): array
    {
        $queryEmbedding = EmbeddingGenerator::generate(
            $query,
            $this->embeddingProvider,
            $this->embeddingModel,
        );

        return $this->vectorStore->query($queryEmbedding, $limit);
    }
}
