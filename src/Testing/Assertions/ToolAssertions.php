<?php

declare(strict_types=1);

namespace Conductor\Testing\Assertions;

use Conductor\Testing\InteractionRecord;
use PHPUnit\Framework\Assert;

trait ToolAssertions
{
    /**
     * Assert that a specific tool was used.
     *
     * @param  string  $toolName  The tool name.
     * @param  int|null  $times  Expected number of times used.
     */
    public function assertToolUsed(string $toolName, ?int $times = null): void
    {
        $count = $this->countToolUses($toolName);

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
     * Assert that a specific tool was not used.
     *
     * @param  string  $toolName  The tool name.
     */
    public function assertToolNotUsed(string $toolName): void
    {
        $count = $this->countToolUses($toolName);

        Assert::assertSame(0, $count, "Tool [{$toolName}] was unexpectedly used {$count} times.");
    }

    /**
     * Count how many times a tool was used across all interactions.
     *
     * @param  string  $toolName  The tool name.
     */
    private function countToolUses(string $toolName): int
    {
        $count = 0;

        foreach ($this->getInteractions() as $record) {
            foreach ($record->response->toolCalls() as $toolCall) {
                if ($toolCall['name'] === $toolName) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Get all interactions.
     *
     * @return array<int, InteractionRecord>
     */
    abstract public function getInteractions(): array;
}
