<?php

declare(strict_types=1);

namespace Conductor\Rag;

use Prism\Prism\Prism;

final class EmbeddingGenerator
{
    /**
     * Generate an embedding for a text string.
     *
     * @param  string  $text  The text to embed.
     * @param  string|null  $provider  The embedding provider.
     * @param  string|null  $model  The embedding model.
     * @return array<int, float>
     */
    public static function generate(string $text, ?string $provider = null, ?string $model = null): array
    {
        $provider = $provider ?? config('conductor.default_provider', 'openai');
        $model = $model ?? 'text-embedding-3-small';

        $response = Prism::embeddings()
            ->using($provider, $model)
            ->fromInput($text)
            ->asEmbeddings();

        $embeddings = $response->embeddings;

        if (count($embeddings) === 0) {
            return [];
        }

        return $embeddings[0]->embedding;
    }

    /**
     * Generate embeddings for multiple texts.
     *
     * @param  array<int, string>  $texts  The texts to embed.
     * @param  string|null  $provider  The embedding provider.
     * @param  string|null  $model  The embedding model.
     * @return array<int, array<int, float>>
     */
    public static function generateBatch(array $texts, ?string $provider = null, ?string $model = null): array
    {
        return array_map(
            fn (string $text): array => self::generate($text, $provider, $model),
            $texts,
        );
    }
}
