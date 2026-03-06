<?php

declare(strict_types=1);

namespace Conductor\Rag\VectorStores;

use Conductor\Contracts\VectorStoreInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class PgVectorStore implements VectorStoreInterface
{
    private readonly string $table;

    private readonly ?string $connection;

    public function __construct()
    {
        $this->table = config('conductor.rag.pgvector.table', 'conductor_embeddings');
        $this->connection = config('conductor.rag.pgvector.connection');
    }

    /**
     * {@inheritDoc}
     */
    public function store(string $id, array $embedding, string $content, array $metadata = []): void
    {
        $vectorString = '['.implode(',', $embedding).']';

        $this->db()->updateOrInsert(
            ['id' => $id],
            [
                'content' => $content,
                'embedding' => $vectorString,
                'metadata' => json_encode($metadata),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    /**
     * {@inheritDoc}
     */
    public function query(array $embedding, int $limit = 5, array $filter = []): array
    {
        $vectorString = '['.implode(',', $embedding).']';

        $query = $this->db()
            ->selectRaw(
                'id, content, metadata, 1 - (embedding <=> ?) as score',
                [$vectorString],
            )
            ->orderByRaw('embedding <=> ?', [$vectorString])
            ->limit($limit);

        $results = $query->get();

        return $results->map(function (object $row): array {
            return [
                'id' => $row->id,
                'content' => $row->content,
                'score' => (float) $row->score,
                'metadata' => json_decode($row->metadata, true) ?? [],
            ];
        })->all();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $id): void
    {
        $this->db()->where('id', $id)->delete();
    }

    /**
     * Get the database query builder.
     */
    private function db(): Builder
    {
        return DB::connection($this->connection)->table($this->table);
    }
}
