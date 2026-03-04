<?php

declare(strict_types=1);

namespace Conductor\Models;

use Illuminate\Database\Eloquent\Model;

final class AgentUsageLog extends Model
{
    /** @var string */
    protected $table = 'conductor_usage_logs';

    /** @var array<int, string> */
    protected $fillable = [
        'agent_name',
        'provider',
        'model',
        'prompt_tokens',
        'completion_tokens',
        'cost_usd',
        'duration_ms',
        'workflow_run_id',
        'workflow_step',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
            'cost_usd' => 'float',
            'duration_ms' => 'integer',
            'metadata' => 'array',
        ];
    }
}
