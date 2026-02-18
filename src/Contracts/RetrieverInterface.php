<?php

declare(strict_types=1);

namespace Conductor\Contracts;

interface RetrieverInterface
{
    /**
     * Retrieve relevant documents for a query.
     *
     * @param  string  $query  The search query.
     * @param  int  $limit  Maximum number of results.
     * @return array<int, array{id: string, content: string, score: float, metadata: array}>
     */
    public function retrieve(string $query, int $limit = 5): array;
}
