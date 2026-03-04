<?php

declare(strict_types=1);

namespace Conductor\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class WorkflowStepRun extends Model
{
    use HasUuids;

    /** @var string */
    protected $table = 'conductor_workflow_step_runs';

    /** @var array<int, string> */
    protected $fillable = [
        'workflow_run_id',
        'step_name',
        'status',
        'input',
        'output',
        'error_message',
        'prompt_tokens',
        'completion_tokens',
        'cost_usd',
        'attempt',
        'duration_ms',
        'started_at',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'input' => 'array',
            'output' => 'array',
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
            'cost_usd' => 'float',
            'attempt' => 'integer',
            'duration_ms' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<WorkflowRun, $this>
     */
    public function workflowRun(): BelongsTo
    {
        return $this->belongsTo(WorkflowRun::class, 'workflow_run_id');
    }
}
