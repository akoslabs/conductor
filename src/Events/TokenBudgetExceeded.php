<?php

declare(strict_types=1);

namespace Conductor\Events;

use Illuminate\Foundation\Events\Dispatchable;

final class TokenBudgetExceeded
{
    use Dispatchable;

    /**
     * @param  string  $context  The agent or workflow name.
     * @param  int  $tokensUsed  The tokens used.
     * @param  int  $budget  The budget that was exceeded.
     */
    public function __construct(
        public readonly string $context,
        public readonly int $tokensUsed,
        public readonly int $budget,
    ) {}
}
