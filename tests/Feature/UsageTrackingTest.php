<?php

declare(strict_types=1);

use Conductor\Facades\Conductor;
use Conductor\Models\AgentUsageLog;
use Prism\Prism\Prism;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\ValueObjects\Meta;
use Prism\Prism\ValueObjects\Usage;

beforeEach(function () {
    $this->artisan('migrate')->assertSuccessful();
});

it('records usage after agent execution', function () {
    Prism::fake([
        TextResponseFake::make()
            ->withText('Tracked response')
            ->withUsage(new Usage(100, 50))
            ->withMeta(new Meta('id', 'claude-sonnet-4-20250514')),
    ]);

    Conductor::agent('tracked-agent')
        ->using('anthropic', 'claude-sonnet-4-20250514')
        ->run('Track this');

    $log = AgentUsageLog::first();

    expect($log)->not->toBeNull()
        ->and($log->agent_name)->toBe('tracked-agent')
        ->and($log->provider)->toBe('anthropic')
        ->and($log->model)->toBe('claude-sonnet-4-20250514')
        ->and($log->prompt_tokens)->toBe(100)
        ->and($log->completion_tokens)->toBe(50);
});
