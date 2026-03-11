<?php

declare(strict_types=1);

namespace Conductor\Testing;

use Conductor\ConductorManager;
use Conductor\Contracts\AgentBuilderInterface;
use Conductor\Contracts\AgentResponseInterface;
use Conductor\Testing\Assertions\AgentAssertions;
use Conductor\Testing\Assertions\ToolAssertions;
use Conductor\Testing\Assertions\UsageAssertions;
use Conductor\Testing\Assertions\WorkflowAssertions;
use Conductor\Workflows\WorkflowBuilder;
use PHPUnit\Framework\Assert;

final class ConductorFake extends ConductorManager
{
    use AgentAssertions;
    use ToolAssertions;
    use UsageAssertions;
    use WorkflowAssertions;

    /** @var array<int, InteractionRecord> */
    private array $interactions = [];

    /** @var array<int, string> */
    private array $completedWorkflows = [];

    /** @var array<string, array<int, string>> */
    private array $completedWorkflowSteps = [];

    /**
     * @param  array<string, string|AgentResponseInterface|FakeSequence|callable>  $responses  Fake responses keyed by agent name.
     */
    public function __construct(
        private readonly array $responses = [],
    ) {}

    /**
     * Create a sequence of responses.
     *
     * @param  array<int, string|AgentResponseInterface|callable>  $responses  The ordered responses.
     */
    public static function sequence(array $responses): FakeSequence
    {
        return new FakeSequence($responses);
    }

    /**
     * {@inheritDoc}
     */
    public function agent(string $name): AgentBuilderInterface
    {
        $response = $this->responses[$name] ?? $this->responses['*'] ?? null;

        return new FakeAgentBuilder($name, $response, $this);
    }

    /**
     * {@inheritDoc}
     */
    public function workflow(string $name): WorkflowBuilder
    {
        return new WorkflowBuilder($name);
    }

    /**
     * Record an agent interaction.
     *
     * @param  string  $agentName  The agent name.
     * @param  string  $input  The user input.
     * @param  AgentResponseInterface  $response  The response.
     */
    public function recordInteraction(string $agentName, string $input, AgentResponseInterface $response): void
    {
        $this->interactions[] = new InteractionRecord(
            agentName: $agentName,
            input: $input,
            response: $response,
            timestamp: microtime(true),
        );
    }

    /**
     * Record a completed workflow.
     *
     * @param  string  $name  The workflow name.
     * @param  array<int, string>  $steps  The completed step names.
     */
    public function recordWorkflowCompleted(string $name, array $steps = []): void
    {
        $this->completedWorkflows[] = $name;
        $this->completedWorkflowSteps[$name] = $steps;
    }

    /**
     * Get all recorded interactions.
     *
     * @return array<int, InteractionRecord>
     */
    public function getInteractions(): array
    {
        return $this->interactions;
    }

    /**
     * {@inheritDoc}
     */
    public function getCompletedWorkflows(): array
    {
        return $this->completedWorkflows;
    }

    /**
     * {@inheritDoc}
     */
    public function getCompletedWorkflowSteps(string $workflowName): array
    {
        return $this->completedWorkflowSteps[$workflowName] ?? [];
    }

    /**
     * {@inheritDoc}
     */
    public function assertAgentCalled(string $name, ?int $times = null): void
    {
        // Delegate to AgentAssertions trait method
        $count = count(array_filter(
            $this->interactions,
            fn (InteractionRecord $record): bool => $record->agentName === $name,
        ));

        Assert::assertGreaterThan(0, $count, "Agent [{$name}] was not called.");

        if ($times !== null) {
            Assert::assertSame(
                $times,
                $count,
                "Agent [{$name}] was called {$count} times, expected {$times}.",
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function assertAgentNotCalled(string $name): void
    {
        $count = count(array_filter(
            $this->interactions,
            fn (InteractionRecord $record): bool => $record->agentName === $name,
        ));

        Assert::assertSame(0, $count, "Agent [{$name}] was unexpectedly called {$count} times.");
    }

    /**
     * {@inheritDoc}
     */
    public function assertToolUsed(string $toolName, ?int $times = null): void
    {
        $count = 0;

        foreach ($this->interactions as $record) {
            foreach ($record->response->toolCalls() as $toolCall) {
                if ($toolCall['name'] === $toolName) {
                    $count++;
                }
            }
        }

        Assert::assertGreaterThan(0, $count, "Tool [{$toolName}] was not used.");

        if ($times !== null) {
            Assert::assertSame(
                $times,
                $count,
                "Tool [{$toolName}] was used {$count} times, expected {$times}.",
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function assertWorkflowCompleted(string $name): void
    {
        Assert::assertContains(
            $name,
            $this->completedWorkflows,
            "Workflow [{$name}] did not complete.",
        );
    }

    /**
     * {@inheritDoc}
     */
    public function assertTokensBelow(int $maxTokens): void
    {
        $total = 0;

        foreach ($this->interactions as $record) {
            $total += $record->response->promptTokens() + $record->response->completionTokens();
        }

        Assert::assertLessThan(
            $maxTokens,
            $total,
            "Total tokens used ({$total}) exceeds maximum ({$maxTokens}).",
        );
    }

    /**
     * {@inheritDoc}
     */
    public function assertNothingCalled(): void
    {
        $count = count($this->interactions);

        Assert::assertSame(0, $count, "{$count} agent interactions were recorded, expected none.");
    }
}
