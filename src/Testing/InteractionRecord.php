<?php

declare(strict_types=1);

namespace Conductor\Testing;

use Conductor\Contracts\AgentResponseInterface;

final readonly class InteractionRecord
{
    /**
     * @param  string  $agentName  The agent name.
     * @param  string  $input  The user input.
     * @param  AgentResponseInterface  $response  The response returned.
     * @param  float  $timestamp  The microtime when the interaction occurred.
     */
    public function __construct(
        public string $agentName,
        public string $input,
        public AgentResponseInterface $response,
        public float $timestamp,
    ) {}
}
