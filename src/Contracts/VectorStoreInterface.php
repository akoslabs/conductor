<?php

declare(strict_types=1);

namespace Conductor\Contracts;

interface VectorStoreInterface
{
    /**
     * Store a document chunk with its embedding vector.
     *
     * @param  string  $id  A unique identifier for this chunk.
     * @param  array<int, float>  $embedding  The embedding vector.
     * @param  string  $content  The original text content of the chunk.
     * @param  array<string, mixed>  $metadata  Additional metadata for the chunk.
     */
    public function store(string $id, array $embedding, string $content, array $metadata = []): void;

    /**
     * Query the vector store for similar documents.
     *
     * @param  array<int, float>  $embedding  The query embedding vector.
     * @param  int  $limit  Maximum number of results to return.
     * @param  array<string, mixed>  $filter  Optional metadata filters.
     * @return array<int, array{id: string, content: string, score: float, metadata: array}>
     */
    public function query(array $embedding, int $limit = 5, array $filter = []): array;

    /**
     * Delete a document chunk by its identifier.
     *
     * @param  string  $id  The chunk identifier to delete.
     */
    public function delete(string $id): void;
}
