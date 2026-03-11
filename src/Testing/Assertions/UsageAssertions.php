<?php

declare(strict_types=1);

namespace Conductor\Testing\Assertions;

use Conductor\Testing\InteractionRecord;
use PHPUnit\Framework\Assert;

trait UsageAssertions
{
    /**
     * Assert that total token usage is below the given maximum.
     *
     * @param  int  $maxTokens  The maximum number of tokens expected.
     */
    public function assertTokensBelow(int $maxTokens): void
    {
        $total = $this->totalTokensUsed();

        Assert::assertLessThan(
            $maxTokens,
            $total,
            "Total tokens used ({$total}) exceeds maximum ({$maxTokens}).",
        );
    }

    /**
     * Assert that total cost is below the given maximum.
     *
     * @param  float  $maxCost  The maximum cost in USD.
     */
    public function assertCostBelow(float $maxCost): void
    {
        $total = $this->totalCost();

        Assert::assertLessThan(
            $maxCost,
            $total,
            "Total cost ({$total}) exceeds maximum ({$maxCost}).",
        );
    }

    /**
     * Calculate total tokens used across all interactions.
     */
    private function totalTokensUsed(): int
    {
        $total = 0;

        foreach ($this->getInteractions() as $record) {
            $total += $record->response->promptTokens() + $record->response->completionTokens();
        }

        return $total;
    }

    /**
     * Calculate total cost across all interactions.
     */
    private function totalCost(): float
    {
        $total = 0.0;

        foreach ($this->getInteractions() as $record) {
            $total += $record->response->costUsd();
        }

        return $total;
    }

    /**
     * Get all interactions.
     *
     * @return array<int, InteractionRecord>
     */
    abstract public function getInteractions(): array;
}
