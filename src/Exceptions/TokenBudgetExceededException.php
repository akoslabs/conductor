<?php

declare(strict_types=1);

namespace Conductor\Exceptions;

use RuntimeException;

final class TokenBudgetExceededException extends RuntimeException
{
    /**
     * Create a new token budget exceeded exception.
     *
     * @param  string  $context  The agent or workflow name.
     * @param  int  $tokensUsed  The number of tokens used.
     * @param  int  $budget  The token budget that was exceeded.
     */
    public function __construct(
        public readonly string $context,
        public readonly int $tokensUsed,
        public readonly int $budget,
    ) {
        parent::__construct(
            "Token budget exceeded for [{$context}]: used {$tokensUsed} tokens, budget was {$budget}.",
        );
    }
}
