<?php

declare(strict_types=1);

namespace Conductor\Monitoring;

use Conductor\Exceptions\TokenBudgetExceededException;
use Conductor\Models\AgentUsageLog;
use Illuminate\Support\Facades\DB;

final class BudgetEnforcer
{
    /**
     * Check if a per-request token budget would be exceeded.
     *
     * @param  string  $context  The agent or workflow name.
     * @param  int  $tokensUsed  The tokens used in this request.
     * @param  int|null  $budget  The budget limit, or null for config default.
     *
     * @throws TokenBudgetExceededException
     */
    public static function checkPerRequestBudget(string $context, int $tokensUsed, ?int $budget = null): void
    {
        $budget = $budget ?? config('conductor.budgets.per_request');

        if ($budget === null) {
            return;
        }

        if ($tokensUsed > $budget) {
            throw new TokenBudgetExceededException($context, $tokensUsed, $budget);
        }
    }

    /**
     * Check if the per-hour budget has been exceeded.
     *
     * @param  string  $context  The agent or workflow name.
     *
     * @throws TokenBudgetExceededException
     */
    public static function checkPerHourBudget(string $context): void
    {
        $budget = config('conductor.budgets.per_hour');

        if ($budget === null) {
            return;
        }

        $tokensUsedLastHour = AgentUsageLog::where('created_at', '>', now()->subHour())
            ->sum(DB::raw('prompt_tokens + completion_tokens'));

        if ($tokensUsedLastHour > $budget) {
            throw new TokenBudgetExceededException($context, (int) $tokensUsedLastHour, $budget);
        }
    }

    /**
     * Check if a per-workflow token budget would be exceeded.
     *
     * @param  string  $context  The workflow name.
     * @param  int  $tokensUsed  Accumulated tokens in the workflow.
     * @param  int|null  $budget  The budget limit.
     *
     * @throws TokenBudgetExceededException
     */
    public static function checkPerWorkflowBudget(string $context, int $tokensUsed, ?int $budget = null): void
    {
        $budget = $budget ?? config('conductor.budgets.per_workflow');

        if ($budget === null) {
            return;
        }

        if ($tokensUsed > $budget) {
            throw new TokenBudgetExceededException($context, $tokensUsed, $budget);
        }
    }
}
