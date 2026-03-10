<?php

declare(strict_types=1);

namespace Conductor\Monitoring;

use Conductor\Models\AgentUsageLog;

final class UsageTracker
{
    /**
     * Record an agent's usage data.
     *
     * @param  string  $agentName  The agent name.
     * @param  string  $provider  The provider used.
     * @param  string  $model  The model used.
     * @param  int  $promptTokens  The number of prompt tokens.
     * @param  int  $completionTokens  The number of completion tokens.
     * @param  float  $costUsd  The estimated cost in USD.
     * @param  int  $durationMs  The execution duration in milliseconds.
     * @param  string|null  $workflowRunId  The workflow run ID, if applicable.
     * @param  string|null  $workflowStep  The workflow step name, if applicable.
     * @param  array<string, mixed>  $metadata  Additional metadata.
     */
    public function track(
        string $agentName,
        string $provider,
        string $model,
        int $promptTokens,
        int $completionTokens,
        float $costUsd,
        int $durationMs,
        ?string $workflowRunId = null,
        ?string $workflowStep = null,
        array $metadata = [],
    ): void {
        AgentUsageLog::create([
            'agent_name' => $agentName,
            'provider' => $provider,
            'model' => $model,
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'cost_usd' => $costUsd,
            'duration_ms' => $durationMs,
            'workflow_run_id' => $workflowRunId,
            'workflow_step' => $workflowStep,
            'metadata' => count($metadata) > 0 ? $metadata : null,
        ]);
    }
}
