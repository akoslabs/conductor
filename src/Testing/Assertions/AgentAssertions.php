<?php

declare(strict_types=1);

namespace Conductor\Testing\Assertions;

use Conductor\Testing\InteractionRecord;
use PHPUnit\Framework\Assert;

trait AgentAssertions
{
    /**
     * Assert that a specific agent was called.
     *
     * @param  string  $name  The agent name.
     * @param  int|null  $times  Expected number of times called.
     */
    public function assertAgentCalled(string $name, ?int $times = null): void
    {
        $count = $this->countAgentCalls($name);

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
     * Assert that a specific agent was not called.
     *
     * @param  string  $name  The agent name.
     */
    public function assertAgentNotCalled(string $name): void
    {
        $count = $this->countAgentCalls($name);

        Assert::assertSame(0, $count, "Agent [{$name}] was unexpectedly called {$count} times.");
    }

    /**
     * Assert that no agents were called.
     */
    public function assertNothingCalled(): void
    {
        $count = count($this->getInteractions());

        Assert::assertSame(0, $count, "{$count} agent interactions were recorded, expected none.");
    }

    /**
     * Count how many times an agent was called.
     *
     * @param  string  $name  The agent name.
     */
    private function countAgentCalls(string $name): int
    {
        return count(array_filter(
            $this->getInteractions(),
            fn (InteractionRecord $record): bool => $record->agentName === $name,
        ));
    }

    /**
     * Get all interactions.
     *
     * @return array<int, InteractionRecord>
     */
    abstract public function getInteractions(): array;
}
