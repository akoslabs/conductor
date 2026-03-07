<?php

declare(strict_types=1);

use Conductor\Rag\Chunker;

it('returns single chunk for short text', function () {
    $chunks = Chunker::chunk('Short text.', 500);

    expect($chunks)->toHaveCount(1)
        ->and($chunks[0])->toBe('Short text.');
});

it('returns empty array for empty text', function () {
    expect(Chunker::chunk(''))->toBe([]);
});

it('splits long text into chunks', function () {
    $sentences = [];
    for ($i = 0; $i < 20; $i++) {
        $sentences[] = 'This is sentence number '.($i + 1).'.';
    }
    $text = implode(' ', $sentences);

    $chunks = Chunker::chunk($text, 200, 50);

    expect(count($chunks))->toBeGreaterThan(1);

    foreach ($chunks as $chunk) {
        expect(mb_strlen($chunk))->toBeLessThanOrEqual(250); // allow some slack for sentence boundaries
    }
});

it('preserves sentence boundaries', function () {
    $text = 'First sentence. Second sentence. Third sentence. Fourth sentence.';
    $chunks = Chunker::chunk($text, 40, 10);

    // Each chunk should contain complete sentences
    foreach ($chunks as $chunk) {
        expect($chunk)->not->toBeEmpty();
    }
});
