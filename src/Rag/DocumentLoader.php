<?php

declare(strict_types=1);

namespace Conductor\Rag;

use InvalidArgumentException;

final class DocumentLoader
{
    /**
     * Load text content from a file path.
     *
     * @param  string  $path  The file path.
     *
     * @throws InvalidArgumentException
     */
    public static function fromFile(string $path): string
    {
        if (! file_exists($path)) {
            throw new InvalidArgumentException("File not found: {$path}");
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw new InvalidArgumentException("Unable to read file: {$path}");
        }

        return $content;
    }

    /**
     * Load text content from a string.
     *
     * @param  string  $text  The text content.
     */
    public static function fromString(string $text): string
    {
        return $text;
    }
}
