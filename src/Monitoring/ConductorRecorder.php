<?php

declare(strict_types=1);

namespace Conductor\Monitoring;

use Conductor\Events\AgentCompleted;
use Conductor\Events\AgentFailed;
use Conductor\Events\ToolExecuted;
use Illuminate\Support\Facades\Event;
use Laravel\Pulse\Facades\Pulse;

final class ConductorRecorder
{
    /**
     * Register event listeners for Pulse recording.
     */
    public function register(): void
    {
        Event::listen(AgentCompleted::class, function (AgentCompleted $event): void {
            $this->recordAgentCompletion($event);
        });

        Event::listen(AgentFailed::class, function (AgentFailed $event): void {
            $this->recordAgentFailure($event);
        });

        Event::listen(ToolExecuted::class, function (ToolExecuted $event): void {
            $this->recordToolExecution($event);
        });
    }

    /**
     * Record a completed agent execution.
     *
     * @param  AgentCompleted  $event  The completion event.
     */
    private function recordAgentCompletion(AgentCompleted $event): void
    {
        if (! class_exists(Pulse::class)) {
            return;
        }

        Pulse::record(
            type: 'conductor_agent',
            key: $event->agentName,
            value: $event->response->promptTokens() + $event->response->completionTokens(),
        )->sum()->count();
    }

    /**
     * Record a failed agent execution.
     *
     * @param  AgentFailed  $event  The failure event.
     */
    private function recordAgentFailure(AgentFailed $event): void
    {
        if (! class_exists(Pulse::class)) {
            return;
        }

        Pulse::record(
            type: 'conductor_agent_error',
            key: $event->agentName,
        )->count();
    }

    /**
     * Record a tool execution.
     *
     * @param  ToolExecuted  $event  The tool event.
     */
    private function recordToolExecution(ToolExecuted $event): void
    {
        if (! class_exists(Pulse::class)) {
            return;
        }

        Pulse::record(
            type: 'conductor_tool',
            key: $event->toolName,
            value: $event->durationMs,
        )->sum()->count();
    }
}
