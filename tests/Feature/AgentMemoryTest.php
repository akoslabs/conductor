<?php

declare(strict_types=1);

use Conductor\Contracts\MemoryStoreInterface;
use Conductor\Facades\Conductor;
use Conductor\Memory\ArrayMemoryStore;
use Prism\Prism\Prism;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\ValueObjects\Meta;
use Prism\Prism\ValueObjects\Usage;

beforeEach(function () {
    $this->artisan('migrate')->assertSuccessful();
});

it('stores conversation in memory across multiple runs', function () {
    $memoryStore = new ArrayMemoryStore;
    app()->instance(MemoryStoreInterface::class, $memoryStore);

    Prism::fake([
        TextResponseFake::make()
            ->withText('First response')
            ->withUsage(new Usage(10, 5))
            ->withMeta(new Meta('id-1', 'model')),
        TextResponseFake::make()
            ->withText('Second response')
            ->withUsage(new Usage(20, 10))
            ->withMeta(new Meta('id-2', 'model')),
    ]);

    Conductor::agent('memory-agent')
        ->using('anthropic', 'claude-sonnet-4-20250514')
        ->withMemory('conv-test')
        ->run('First message');

    $messages = $memoryStore->retrieve('conv-test', 'memory-agent');
    expect($messages)->toHaveCount(2)
        ->and($messages[0]['role'])->toBe('user')
        ->and($messages[0]['content'])->toBe('First message')
        ->and($messages[1]['role'])->toBe('assistant')
        ->and($messages[1]['content'])->toBe('First response');

    Conductor::agent('memory-agent')
        ->using('anthropic', 'claude-sonnet-4-20250514')
        ->withMemory('conv-test')
        ->run('Second message');

    $messages = $memoryStore->retrieve('conv-test', 'memory-agent');
    expect($messages)->toHaveCount(4);
});
