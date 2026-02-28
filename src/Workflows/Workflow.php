<?php

declare(strict_types=1);

namespace Conductor\Workflows;

use Conductor\Contracts\WorkflowInterface;
use Conductor\Contracts\WorkflowResultInterface;
use Conductor\Facades\Conductor;

abstract class Workflow implements WorkflowInterface
{
    protected string $name = '';

    protected ?int $tokenBudget = null;

    /**
     * {@inheritDoc}
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    abstract public function steps(): array;

    /**
     * {@inheritDoc}
     */
    public static function start(string $input, array $metadata = []): WorkflowResultInterface
    {
        $instance = app(static::class);

        $builder = Conductor::workflow($instance->name())
            ->withMetadata($metadata);

        if ($instance->tokenBudget !== null) {
            $builder->withTokenBudget($instance->tokenBudget);
        }

        foreach ($instance->steps() as $step) {
            if ($step instanceof WorkflowStep) {
                $builder->step(
                    name: $step->name,
                    callable: $step->callable,
                    dependsOn: $step->dependsOn,
                    retries: $step->retries,
                    backoffMs: $step->backoffMs,
                );
            }
        }

        return $builder->start($input);
    }

    /**
     * {@inheritDoc}
     */
    public static function resume(string $runId, array $data = []): WorkflowResultInterface
    {
        $instance = app(static::class);

        $builder = Conductor::workflow($instance->name());

        if ($instance->tokenBudget !== null) {
            $builder->withTokenBudget($instance->tokenBudget);
        }

        foreach ($instance->steps() as $step) {
            if ($step instanceof WorkflowStep) {
                $builder->step(
                    name: $step->name,
                    callable: $step->callable,
                    dependsOn: $step->dependsOn,
                    retries: $step->retries,
                    backoffMs: $step->backoffMs,
                );
            }
        }

        return $builder->resume($runId, $data);
    }
}
