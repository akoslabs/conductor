<?php

declare(strict_types=1);

namespace Conductor\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class WorkflowRun extends Model
{
    use HasUuids;

    /** @var string */
    protected $table = 'conductor_workflow_runs';

    /** @var array<int, string> */
    protected $fillable = [
        'workflow_name',
        'status',
        'state',
        'input',
        'output',
        'current_step',
        'error_message',
        'total_tokens',
        'total_cost_usd',
        'started_at',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'state' => 'array',
            'input' => 'array',
            'output' => 'array',
            'total_tokens' => 'integer',
            'total_cost_usd' => 'float',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<WorkflowStepRun, $this>
     */
    public function stepRuns(): HasMany
    {
        return $this->hasMany(WorkflowStepRun::class, 'workflow_run_id');
    }
}
