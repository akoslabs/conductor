<?php

declare(strict_types=1);

namespace Conductor\Testing;

use Conductor\Contracts\AgentResponseInterface;
use RuntimeException;

final class FakeSequence
{
    private int $index = 0;

    /**
     * @param  array<int, string|AgentResponseInterface|callable>  $responses  The ordered responses.
     */
    public function __construct(
        private readonly array $responses,
    ) {}

    /**
     * Get the next response in the sequence.
     *
     * @param  string  $input  The user input (passed to callable responses).
     *
     * @throws RuntimeException
     */
    public function next(string $input): AgentResponseInterface
    {
        if ($this->index >= count($this->responses)) {
            throw new RuntimeException('Fake sequence exhausted. No more responses available.');
        }

        $response = $this->responses[$this->index];
        $this->index++;

        if (is_string($response)) {
            return FakeAgentResponse::fromString($response);
        }

        if (is_callable($response)) {
            $result = $response($input);

            return is_string($result) ? FakeAgentResponse::fromString($result) : $result;
        }

        return $response;
    }

    /**
     * Check if the sequence is exhausted.
     */
    public function isEmpty(): bool
    {
        return $this->index >= count($this->responses);
    }
}
