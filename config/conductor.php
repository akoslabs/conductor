<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Provider
    |--------------------------------------------------------------------------
    | The default Prism provider and model for agents that don't specify one.
    */
    'default_provider' => env('CONDUCTOR_PROVIDER', 'anthropic'),
    'default_model' => env('CONDUCTOR_MODEL', 'claude-sonnet-4-20250514'),

    /*
    |--------------------------------------------------------------------------
    | Memory
    |--------------------------------------------------------------------------
    | How agents store conversation history.
    | Supported: "database", "cache", "array"
    */
    'memory' => [
        'driver' => env('CONDUCTOR_MEMORY_DRIVER', 'database'),
        'max_messages' => 50,
        'database' => [
            'connection' => null,
            'table' => 'conductor_conversation_messages',
        ],
        'cache' => [
            'store' => null,
            'ttl' => 3600,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Workflows
    |--------------------------------------------------------------------------
    */
    'workflows' => [
        'database' => [
            'connection' => null,
            'runs_table' => 'conductor_workflow_runs',
            'steps_table' => 'conductor_workflow_step_runs',
        ],
        'default_retry_attempts' => 3,
        'default_retry_backoff_ms' => 1000,
    ],

    /*
    |--------------------------------------------------------------------------
    | RAG (Retrieval-Augmented Generation)
    |--------------------------------------------------------------------------
    */
    'rag' => [
        'vector_store' => env('CONDUCTOR_VECTOR_STORE', 'memory'),
        'chunk_size' => 500,
        'chunk_overlap' => 50,
        'pgvector' => [
            'connection' => null,
            'table' => 'conductor_embeddings',
            'dimensions' => 1536,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Usage Tracking
    |--------------------------------------------------------------------------
    */
    'usage' => [
        'enabled' => true,
        'database' => [
            'connection' => null,
            'table' => 'conductor_usage_logs',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Budgets
    |--------------------------------------------------------------------------
    | Hard limits to prevent runaway costs. Set to null to disable.
    */
    'budgets' => [
        'per_request' => env('CONDUCTOR_BUDGET_PER_REQUEST', null),
        'per_workflow' => env('CONDUCTOR_BUDGET_PER_WORKFLOW', null),
        'per_hour' => env('CONDUCTOR_BUDGET_PER_HOUR', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Providers
    |--------------------------------------------------------------------------
    | If the primary provider fails, try these in order.
    */
    'fallbacks' => [
        // ['provider' => 'openai', 'model' => 'gpt-4o'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard (Premium)
    |--------------------------------------------------------------------------
    */
    'dashboard' => [
        'enabled' => false,
        'path' => 'conductor',
        'middleware' => ['web', 'auth'],
    ],
];
