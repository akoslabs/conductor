<?php

declare(strict_types=1);

namespace Conductor\Rag\VectorStores;

use Conductor\Contracts\VectorStoreInterface;

final class InMemoryVectorStore implements VectorStoreInterface
{
    /** @var array<string, array{embedding: array<int, float>, content: string, metadata: array<string, mixed>}> */
    private array $documents = [];

    /**
     * {@inheritDoc}
     */
    public function store(string $id, array $embedding, string $content, array $metadata = []): void
    {
        $this->documents[$id] = [
            'embedding' => $embedding,
            'content' => $content,
            'metadata' => $metadata,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function query(array $embedding, int $limit = 5, array $filter = []): array
    {
        $results = [];

        foreach ($this->documents as $id => $document) {
            if (! $this->matchesFilter($document['metadata'], $filter)) {
                continue;
            }

            $score = $this->cosineSimilarity($embedding, $document['embedding']);

            $results[] = [
                'id' => $id,
                'content' => $document['content'],
                'score' => $score,
                'metadata' => $document['metadata'],
            ];
        }

        usort($results, fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        return array_slice($results, 0, $limit);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $id): void
    {
        unset($this->documents[$id]);
    }

    /**
     * Calculate cosine similarity between two vectors.
     *
     * @param  array<int, float>  $a  The first vector.
     * @param  array<int, float>  $b  The second vector.
     */
    private function cosineSimilarity(array $a, array $b): float
    {
        $dotProduct = 0.0;
        $magnitudeA = 0.0;
        $magnitudeB = 0.0;

        $length = min(count($a), count($b));

        for ($i = 0; $i < $length; $i++) {
            $dotProduct += $a[$i] * $b[$i];
            $magnitudeA += $a[$i] * $a[$i];
            $magnitudeB += $b[$i] * $b[$i];
        }

        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);

        if ($magnitudeA == 0.0 || $magnitudeB == 0.0) {
            return 0.0;
        }

        return $dotProduct / ($magnitudeA * $magnitudeB);
    }

    /**
     * Check if metadata matches filter criteria.
     *
     * @param  array<string, mixed>  $metadata
     * @param  array<string, mixed>  $filter
     */
    private function matchesFilter(array $metadata, array $filter): bool
    {
        foreach ($filter as $key => $value) {
            if (! isset($metadata[$key]) || $metadata[$key] !== $value) {
                return false;
            }
        }

        return true;
    }
}
