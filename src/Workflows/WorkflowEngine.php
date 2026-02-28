<?php

declare(strict_types=1);

namespace Conductor\Workflows;

use Conductor\Contracts\AgentResponseInterface;
use Conductor\Contracts\WorkflowResultInterface;
use Conductor\Enums\WorkflowStepStatus;
use Conductor\Events\WorkflowCompleted;
use Conductor\Events\WorkflowFailed;
use Conductor\Events\WorkflowPaused;
use Conductor\Events\WorkflowStarted;
use Conductor\Events\WorkflowStepCompleted;
use Conductor\Exceptions\TokenBudgetExceededException;
use Conductor\Exceptions\WorkflowException;
use Conductor\Models\WorkflowRun;
use Conductor\Models\WorkflowStepRun;
use Throwable;

final class WorkflowEngine
{
    /**
     * Execute a workflow with the given steps.
     *
     * @param  string  $workflowName  The workflow name.
     * @param  array<int, WorkflowStep>  $steps  The workflow steps.
     * @param  string  $input  The workflow input.
     * @param  array<string, mixed>  $metadata  Additional metadata.
     * @param  int|null  $tokenBudget  Optional per-workflow token budget.
     *
     * @throws WorkflowException
     */
    public function execute(
        string $workflowName,
        array $steps,
        string $input,
        array $metadata = [],
        ?int $tokenBudget = null,
    ): WorkflowResultInterface {
        event(new WorkflowStarted($workflowName, $input));

        $run = WorkflowRun::create([
            'workflow_name' => $workflowName,
            'status' => 'running',
            'input' => ['text' => $input],
            'started_at' => now(),
        ]);

        $state = new WorkflowState($input, $metadata);
        $sortedSteps = $this->topologicalSort($steps);
        $totalTokens = 0;
        $totalCost = 0.0;
        $stepsCompleted = 0;

        $budget = $tokenBudget ?? config('conductor.budgets.per_workflow');

        try {
            foreach ($sortedSteps as $step) {
                if ($step->condition !== null && ! ($step->condition)($state)) {
                    WorkflowStepRun::create([
                        'workflow_run_id' => $run->id,
                        'step_name' => $step->name,
                        'status' => WorkflowStepStatus::Skipped->value,
                        'started_at' => now(),
                        'completed_at' => now(),
                    ]);

                    continue;
                }

                if ($budget !== null && $totalTokens > $budget) {
                    throw new TokenBudgetExceededException($workflowName, $totalTokens, $budget);
                }

                $run->update(['current_step' => $step->name]);

                $stepOutput = $this->executeStep($run, $step, $state);

                if ($stepOutput instanceof AgentResponseInterface) {
                    $stepTokens = $stepOutput->promptTokens() + $stepOutput->completionTokens();
                    $totalTokens += $stepTokens;
                    $totalCost += $stepOutput->costUsd();
                    $state->setStepOutput($step->name, $stepOutput->text());
                } else {
                    $state->setStepOutput($step->name, $stepOutput);
                }

                $stepsCompleted++;

                event(new WorkflowStepCompleted($workflowName, $step->name, $state->getStepOutput($step->name)));

                if ($step->requiresHumanApproval) {
                    $run->update([
                        'status' => 'paused',
                        'state' => $state->toArray(),
                        'current_step' => $step->name,
                        'total_tokens' => $totalTokens,
                        'total_cost_usd' => $totalCost,
                    ]);

                    event(new WorkflowPaused($workflowName, $step->name, $run->id));

                    return new WorkflowResult(
                        finalOutput: $state->getStepOutput($step->name),
                        totalTokens: $totalTokens,
                        totalCostUsd: $totalCost,
                        stepsCompleted: $stepsCompleted,
                        status: 'paused',
                        runId: $run->id,
                    );
                }
            }

            $lastOutput = count($sortedSteps) > 0
                ? $state->getStepOutput($sortedSteps[array_key_last($sortedSteps)]->name)
                : $input;

            $run->update([
                'status' => 'completed',
                'output' => is_string($lastOutput) ? ['text' => $lastOutput] : $lastOutput,
                'total_tokens' => $totalTokens,
                'total_cost_usd' => $totalCost,
                'completed_at' => now(),
            ]);

            $result = new WorkflowResult(
                finalOutput: $lastOutput,
                totalTokens: $totalTokens,
                totalCostUsd: $totalCost,
                stepsCompleted: $stepsCompleted,
                status: 'completed',
                runId: $run->id,
            );

            event(new WorkflowCompleted($workflowName, $result));

            return $result;
        } catch (TokenBudgetExceededException $e) {
            $run->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'total_tokens' => $totalTokens,
                'total_cost_usd' => $totalCost,
            ]);

            throw $e;
        } catch (Throwable $e) {
            $run->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'total_tokens' => $totalTokens,
                'total_cost_usd' => $totalCost,
            ]);

            event(new WorkflowFailed($workflowName, $e));

            throw new WorkflowException(
                workflowName: $workflowName,
                message: $e->getMessage(),
                previous: $e,
            );
        }
    }

    /**
     * Resume a paused workflow.
     *
     * @param  string  $runId  The workflow run UUID.
     * @param  array<string, mixed>  $data  Data to provide to the next step.
     * @param  array<int, WorkflowStep>  $steps  The workflow steps.
     * @param  int|null  $tokenBudget  Optional per-workflow token budget.
     *
     * @throws WorkflowException
     */
    public function resume(
        string $runId,
        array $data,
        array $steps,
        ?int $tokenBudget = null,
    ): WorkflowResultInterface {
        $run = WorkflowRun::findOrFail($runId);

        if ($run->status !== 'paused') {
            throw new WorkflowException($run->workflow_name, 'Workflow is not paused.');
        }

        $state = WorkflowState::fromArray($run->state ?? []);

        if (isset($data['approval'])) {
            $state->setStepOutput($run->current_step.'_approval', $data['approval']);
        }

        foreach ($data as $key => $value) {
            if ($key !== 'approval') {
                $state->setStepOutput($run->current_step.'_'.$key, $value);
            }
        }

        $sortedSteps = $this->topologicalSort($steps);
        $foundPausedStep = false;
        $totalTokens = $run->total_tokens ?? 0;
        $totalCost = $run->total_cost_usd ?? 0.0;
        $stepsCompleted = 0;

        $budget = $tokenBudget ?? config('conductor.budgets.per_workflow');

        $run->update(['status' => 'running']);

        try {
            foreach ($sortedSteps as $step) {
                if (! $foundPausedStep) {
                    if ($step->name === $run->current_step) {
                        $foundPausedStep = true;
                    }

                    continue;
                }

                if ($step->condition !== null && ! ($step->condition)($state)) {
                    continue;
                }

                if ($budget !== null && $totalTokens > $budget) {
                    throw new TokenBudgetExceededException($run->workflow_name, $totalTokens, $budget);
                }

                $run->update(['current_step' => $step->name]);

                $stepOutput = $this->executeStep($run, $step, $state);

                if ($stepOutput instanceof AgentResponseInterface) {
                    $totalTokens += $stepOutput->promptTokens() + $stepOutput->completionTokens();
                    $totalCost += $stepOutput->costUsd();
                    $state->setStepOutput($step->name, $stepOutput->text());
                } else {
                    $state->setStepOutput($step->name, $stepOutput);
                }

                $stepsCompleted++;

                event(new WorkflowStepCompleted($run->workflow_name, $step->name, $state->getStepOutput($step->name)));

                if ($step->requiresHumanApproval) {
                    $run->update([
                        'status' => 'paused',
                        'state' => $state->toArray(),
                        'current_step' => $step->name,
                        'total_tokens' => $totalTokens,
                        'total_cost_usd' => $totalCost,
                    ]);

                    event(new WorkflowPaused($run->workflow_name, $step->name, $run->id));

                    return new WorkflowResult(
                        finalOutput: $state->getStepOutput($step->name),
                        totalTokens: $totalTokens,
                        totalCostUsd: $totalCost,
                        stepsCompleted: $stepsCompleted,
                        status: 'paused',
                        runId: $run->id,
                    );
                }
            }

            $lastOutput = count($sortedSteps) > 0
                ? $state->getStepOutput($sortedSteps[array_key_last($sortedSteps)]->name)
                : '';

            $run->update([
                'status' => 'completed',
                'output' => is_string($lastOutput) ? ['text' => $lastOutput] : $lastOutput,
                'total_tokens' => $totalTokens,
                'total_cost_usd' => $totalCost,
                'completed_at' => now(),
            ]);

            $result = new WorkflowResult(
                finalOutput: $lastOutput,
                totalTokens: $totalTokens,
                totalCostUsd: $totalCost,
                stepsCompleted: $stepsCompleted,
                status: 'completed',
                runId: $run->id,
            );

            event(new WorkflowCompleted($run->workflow_name, $result));

            return $result;
        } catch (Throwable $e) {
            $run->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            event(new WorkflowFailed($run->workflow_name, $e));

            throw new WorkflowException(
                workflowName: $run->workflow_name,
                message: $e->getMessage(),
                previous: $e,
            );
        }
    }

    /**
     * Execute a single step with retry support.
     *
     * @param  WorkflowRun  $run  The workflow run.
     * @param  WorkflowStep  $step  The step to execute.
     * @param  WorkflowState  $state  The current state.
     *
     * @throws Throwable
     */
    private function executeStep(WorkflowRun $run, WorkflowStep $step, WorkflowState $state): mixed
    {
        $maxAttempts = $step->retries + 1;
        $lastException = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $stepRun = WorkflowStepRun::create([
                'workflow_run_id' => $run->id,
                'step_name' => $step->name,
                'status' => WorkflowStepStatus::Running->value,
                'attempt' => $attempt,
                'started_at' => now(),
            ]);

            try {
                $startTime = hrtime(true);
                $output = ($step->callable)($state);
                $durationMs = (int) ((hrtime(true) - $startTime) / 1_000_000);

                $updateData = [
                    'status' => WorkflowStepStatus::Completed->value,
                    'duration_ms' => $durationMs,
                    'completed_at' => now(),
                ];

                if ($output instanceof AgentResponseInterface) {
                    $updateData['prompt_tokens'] = $output->promptTokens();
                    $updateData['completion_tokens'] = $output->completionTokens();
                    $updateData['cost_usd'] = $output->costUsd();
                    $updateData['output'] = ['text' => $output->text()];
                } else {
                    $updateData['output'] = is_string($output) ? ['text' => $output] : $output;
                }

                $stepRun->update($updateData);

                return $output;
            } catch (Throwable $e) {
                $lastException = $e;

                $stepRun->update([
                    'status' => WorkflowStepStatus::Failed->value,
                    'error_message' => $e->getMessage(),
                    'completed_at' => now(),
                ]);

                if ($attempt < $maxAttempts) {
                    usleep($step->backoffMs * 1000);
                }
            }
        }

        throw $lastException;
    }

    /**
     * Topologically sort steps based on dependencies.
     *
     * @param  array<int, WorkflowStep>  $steps
     * @return array<int, WorkflowStep>
     *
     * @throws WorkflowException
     */
    private function topologicalSort(array $steps): array
    {
        $stepMap = [];
        foreach ($steps as $step) {
            $stepMap[$step->name] = $step;
        }

        $sorted = [];
        $visited = [];
        $visiting = [];

        foreach ($stepMap as $name => $step) {
            if (! isset($visited[$name])) {
                $this->visitStep($name, $stepMap, $visited, $visiting, $sorted);
            }
        }

        return $sorted;
    }

    /**
     * DFS visit for topological sort.
     *
     * @param  string  $name  The step name.
     * @param  array<string, WorkflowStep>  $stepMap
     * @param  array<string, bool>  $visited
     * @param  array<string, bool>  $visiting
     * @param  array<int, WorkflowStep>  $sorted
     *
     * @throws WorkflowException
     */
    private function visitStep(string $name, array $stepMap, array &$visited, array &$visiting, array &$sorted): void
    {
        if (isset($visiting[$name])) {
            throw new WorkflowException('unknown', "Circular dependency detected at step [{$name}].");
        }

        if (isset($visited[$name])) {
            return;
        }

        $visiting[$name] = true;

        $step = $stepMap[$name] ?? null;
        if ($step === null) {
            return;
        }

        foreach ($step->dependsOn as $dependency) {
            $this->visitStep($dependency, $stepMap, $visited, $visiting, $sorted);
        }

        unset($visiting[$name]);
        $visited[$name] = true;
        $sorted[] = $step;
    }
}
