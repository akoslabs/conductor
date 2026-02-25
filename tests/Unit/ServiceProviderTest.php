<?php

declare(strict_types=1);

use Conductor\ConductorManager;
use Conductor\Facades\Conductor;

it('registers the conductor manager as a singleton', function () {
    $manager = app(ConductorManager::class);

    expect($manager)->toBeInstanceOf(ConductorManager::class);
    expect(app(ConductorManager::class))->toBe($manager);
});

it('merges the config', function () {
    $config = config('conductor');

    expect($config)->toBeArray()
        ->and($config)->toHaveKeys([
            'default_provider',
            'default_model',
            'memory',
            'workflows',
            'rag',
            'usage',
            'budgets',
            'fallbacks',
            'dashboard',
        ]);
});

it('registers the facade', function () {
    $resolved = Conductor::getFacadeRoot();

    expect($resolved)->toBeInstanceOf(ConductorManager::class);
});
