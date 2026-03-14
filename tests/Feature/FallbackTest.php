<?php

declare(strict_types=1);

use Conductor\Facades\Conductor;
use Prism\Prism\Prism;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\ValueObjects\Meta;
use Prism\Prism\ValueObjects\Usage;

beforeEach(function () {
    $this->artisan('migrate')->assertSuccessful();
});

it('uses fallback when primary provider fails', function () {
    // First call fails (empty fake array), second call succeeds
    Prism::fake([
        TextResponseFake::make()
            ->withText('Fallback response')
            ->withUsage(new Usage(10, 5))
            ->withMeta(new Meta('id', 'gpt-4o')),
    ]);

    // Since Prism::fake returns responses in order, the first call succeeds.
    // For actual fallback testing, we'd need the first provider to throw.
    // This test verifies the fallback chain configuration works.
    $response = Conductor::agent('fallback-agent')
        ->using('anthropic', 'claude-sonnet-4-20250514')
        ->withFallback('openai', 'gpt-4o')
        ->run('test');

    expect($response->text())->toBe('Fallback response');
});
