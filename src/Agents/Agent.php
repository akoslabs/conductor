<?php

declare(strict_types=1);

namespace Conductor\Agents;

use Conductor\Contracts\AgentInterface;
use Conductor\Contracts\AgentResponseInterface;
use Conductor\Facades\Conductor;
use Generator;

abstract class Agent implements AgentInterface
{
    protected string $name = '';

    protected string $description = '';

    protected string $provider = 'anthropic';

    protected string $model = 'claude-sonnet-4-20250514';

    protected ?int $tokenBudget = null;

    protected int $maxSteps = 1;

    /**
     * {@inheritDoc}
     */
    abstract public function systemPrompt(): string;

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
    public function tools(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function memory(): ?string
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public static function run(string $input, ?string $conversationId = null): AgentResponseInterface
    {
        $instance = app(static::class);

        $builder = Conductor::agent($instance->name())
            ->using($instance->provider, $instance->model)
            ->withSystemPrompt($instance->systemPrompt())
            ->withMaxSteps($instance->maxSteps);

        if (count($instance->tools()) > 0) {
            $builder->withTools($instance->tools());
        }

        if ($instance->tokenBudget !== null) {
            $builder->withTokenBudget($instance->tokenBudget);
        }

        if ($instance->memory() !== null || $conversationId !== null) {
            $builder->withMemory($conversationId);
        }

        return $builder->run($input);
    }

    /**
     * {@inheritDoc}
     */
    public static function stream(string $input, ?string $conversationId = null): Generator
    {
        $instance = app(static::class);

        $builder = Conductor::agent($instance->name())
            ->using($instance->provider, $instance->model)
            ->withSystemPrompt($instance->systemPrompt())
            ->withMaxSteps($instance->maxSteps);

        if (count($instance->tools()) > 0) {
            $builder->withTools($instance->tools());
        }

        if ($instance->tokenBudget !== null) {
            $builder->withTokenBudget($instance->tokenBudget);
        }

        if ($instance->memory() !== null || $conversationId !== null) {
            $builder->withMemory($conversationId);
        }

        return $builder->stream($input);
    }
}
