<?php

declare(strict_types=1);

namespace Conductor\Agents\Concerns;

use Conductor\Contracts\AgentResponseInterface;

trait TracksUsage
{
    /**
     * Get the total token count from a response.
     *
     * @param  AgentResponseInterface  $response  The agent response.
     */
    protected function totalTokens(AgentResponseInterface $response): int
    {
        return $response->promptTokens() + $response->completionTokens();
    }
}
