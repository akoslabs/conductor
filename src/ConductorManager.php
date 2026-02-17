<?php

declare(strict_types=1);

namespace Conductor;

use Conductor\Agents\AgentBuilder;
use Conductor\Contracts\AgentBuilderInterface;
use Conductor\Facades\Conductor;
use Conductor\Testing\ConductorFake;
use Conductor\Workflows\WorkflowBuilder;

/**
 * The main Conductor service class, registered as a singleton behind the Conductor facade.
 */
class ConductorManager
{
    /**
     * Create a new agent builder instance.
     *
     * @param  string  $name  The agent name identifier.
     */
    public function agent(string $name): AgentBuilderInterface
    {
        return new AgentBuilder($name);
    }

    /**
     * Create a new workflow builder instance.
     *
     * @param  string  $name  The workflow name identifier.
     */
    public function workflow(string $name): WorkflowBuilder
    {
        return new WorkflowBuilder($name);
    }

    /**
     * Replace the Conductor instance with a fake for testing.
     *
     * @param  array<string, string|array<string, mixed>|callable>  $responses  Fake responses keyed by agent name.
     */
    public function fake(array $responses = []): ConductorFake
    {
        $fake = new ConductorFake($responses);

        app()->instance(self::class, $fake);

        Conductor::clearResolvedInstances();

        return $fake;
    }

    /**
     * Assert that a specific agent was called.
     *
     * @param  string  $name  The agent name.
     * @param  int|null  $times  Expected number of times called.
     */
    public function assertAgentCalled(string $name, ?int $times = null): void
    {
        throw new \BadMethodCallException('Cannot assert on a non-fake instance. Call Conductor::fake() first.');
    }

    /**
     * Assert that a specific agent was not called.
     *
     * @param  string  $name  The agent name.
     */
    public function assertAgentNotCalled(string $name): void
    {
        throw new \BadMethodCallException('Cannot assert on a non-fake instance. Call Conductor::fake() first.');
    }

    /**
     * Assert that a specific tool was used.
     *
     * @param  string  $toolName  The tool name.
     * @param  int|null  $times  Expected number of times used.
     */
    public function assertToolUsed(string $toolName, ?int $times = null): void
    {
        throw new \BadMethodCallException('Cannot assert on a non-fake instance. Call Conductor::fake() first.');
    }

    /**
     * Assert that a specific workflow completed successfully.
     *
     * @param  string  $name  The workflow name.
     */
    public function assertWorkflowCompleted(string $name): void
    {
        throw new \BadMethodCallException('Cannot assert on a non-fake instance. Call Conductor::fake() first.');
    }

    /**
     * Assert that token usage is below the given maximum.
     *
     * @param  int  $maxTokens  The maximum number of tokens expected.
     */
    public function assertTokensBelow(int $maxTokens): void
    {
        throw new \BadMethodCallException('Cannot assert on a non-fake instance. Call Conductor::fake() first.');
    }

    /**
     * Assert that no agents were called.
     */
    public function assertNothingCalled(): void
    {
        throw new \BadMethodCallException('Cannot assert on a non-fake instance. Call Conductor::fake() first.');
    }
}
