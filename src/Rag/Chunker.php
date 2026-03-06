<?php

declare(strict_types=1);

namespace Conductor\Rag;

final class Chunker
{
    /**
     * Split text into overlapping chunks.
     *
     * @param  string  $text  The text to chunk.
     * @param  int  $chunkSize  Maximum characters per chunk.
     * @param  int  $overlap  Number of overlapping characters between chunks.
     * @return array<int, string>
     */
    public static function chunk(string $text, int $chunkSize = 500, int $overlap = 50): array
    {
        $text = trim($text);

        if ($text === '') {
            return [];
        }

        if (mb_strlen($text) <= $chunkSize) {
            return [$text];
        }

        $sentences = self::splitIntoSentences($text);
        $chunks = [];
        $currentChunk = '';
        $previousOverlap = '';

        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if ($sentence === '') {
                continue;
            }

            $wouldBeLength = mb_strlen($currentChunk) + mb_strlen($sentence) + ($currentChunk !== '' ? 1 : 0);

            if ($wouldBeLength > $chunkSize && $currentChunk !== '') {
                $chunks[] = trim($currentChunk);

                $previousOverlap = mb_strlen($currentChunk) > $overlap
                    ? mb_substr($currentChunk, -$overlap)
                    : $currentChunk;

                $currentChunk = trim($previousOverlap.' '.$sentence);
            } else {
                $currentChunk .= ($currentChunk !== '' ? ' ' : '').$sentence;
            }
        }

        if (trim($currentChunk) !== '') {
            $chunks[] = trim($currentChunk);
        }

        return $chunks;
    }

    /**
     * Split text into sentences at sentence-ending punctuation.
     *
     * @param  string  $text  The text to split.
     * @return array<int, string>
     */
    private static function splitIntoSentences(string $text): array
    {
        $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        return $sentences ?: [$text];
    }
}
