<?php

declare(strict_types=1);

namespace Conductor\Agents;

use Generator;
use Prism\Prism\Text\Chunk;

final class StreamingResponse
{
    private string $accumulatedText = '';

    /**
     * @param  Generator<Chunk>  $chunks  The streaming chunks from Prism.
     */
    public function __construct(
        private readonly Generator $chunks,
    ) {}

    /**
     * Yield formatted chunks from the stream.
     *
     * @return Generator<int, array{text: string, is_final: bool}>
     */
    public function stream(): Generator
    {
        foreach ($this->chunks as $chunk) {
            $this->accumulatedText .= $chunk->text;

            yield [
                'text' => $chunk->text,
                'is_final' => $chunk->finishReason !== null,
            ];
        }
    }

    /**
     * Get the full accumulated text after streaming completes.
     */
    public function fullText(): string
    {
        return $this->accumulatedText;
    }
}
