<?php

declare(strict_types=1);

namespace Conductor;

use Conductor\Console\MakeAgentCommand;
use Conductor\Console\MakeToolCommand;
use Conductor\Console\MakeWorkflowCommand;
use Conductor\Contracts\MemoryStoreInterface;
use Conductor\Contracts\VectorStoreInterface;
use Conductor\Memory\ArrayMemoryStore;
use Conductor\Memory\CacheMemoryStore;
use Conductor\Memory\DatabaseMemoryStore;
use Conductor\Monitoring\ConductorRecorder;
use Conductor\Monitoring\UsageTracker;
use Conductor\Rag\VectorStores\InMemoryVectorStore;
use Conductor\Rag\VectorStores\PgVectorStore;
use Illuminate\Support\ServiceProvider;
use Laravel\Pulse\Facades\Pulse;

final class ConductorServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/conductor.php', 'conductor');

        $this->app->singleton(ConductorManager::class);

        $this->app->singleton(MemoryStoreInterface::class, function (): MemoryStoreInterface {
            return match (config('conductor.memory.driver', 'database')) {
                'cache' => new CacheMemoryStore,
                'array' => new ArrayMemoryStore,
                default => new DatabaseMemoryStore,
            };
        });

        $this->app->singleton(VectorStoreInterface::class, function (): VectorStoreInterface {
            return match (config('conductor.rag.vector_store', 'memory')) {
                'pgvector' => new PgVectorStore,
                default => new InMemoryVectorStore,
            };
        });

        $this->app->singleton(UsageTracker::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/conductor.php' => config_path('conductor.php'),
        ], 'conductor-config');

        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'conductor-migrations');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeAgentCommand::class,
                MakeToolCommand::class,
                MakeWorkflowCommand::class,
            ]);
        }

        if (class_exists(Pulse::class)) {
            $recorder = new ConductorRecorder;
            $recorder->register();
        }
    }
}
