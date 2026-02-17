<?php

declare(strict_types=1);

namespace Conductor\Facades;

use Conductor\ConductorManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Conductor\Contracts\AgentBuilderInterface agent(string $name)
 * @method static mixed workflow(string $name)
 * @method static \Conductor\Testing\ConductorFake fake(array $responses = [])
 * @method static void assertAgentCalled(string $name, ?int $times = null)
 * @method static void assertAgentNotCalled(string $name)
 * @method static void assertToolUsed(string $toolName, ?int $times = null)
 * @method static void assertWorkflowCompleted(string $name)
 * @method static void assertTokensBelow(int $maxTokens)
 *
 * @see ConductorManager
 */
final class Conductor extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return ConductorManager::class;
    }
}
