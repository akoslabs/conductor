<?php

declare(strict_types=1);

namespace Conductor\Workflows;

final class WorkflowState
{
    /** @var array<string, mixed> */
    private array $stepOutputs = [];

    /**
     * @param  string  $input  The workflow input.
     * @param  array<string, mixed>  $metadata  Additional metadata.
     */
    public function __construct(
        private readonly string $input,
        private readonly array $metadata = [],
    ) {}

    /**
     * Get the workflow input.
     */
    public function input(): string
    {
        return $this->input;
    }

    /**
     * Get the workflow metadata.
     *
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }

    /**
     * Set the output of a step.
     *
     * @param  string  $stepName  The step name.
     * @param  mixed  $output  The step output.
     */
    public function setStepOutput(string $stepName, mixed $output): void
    {
        $this->stepOutputs[$stepName] = $output;
    }

    /**
     * Get the output of a step.
     *
     * @param  string  $stepName  The step name.
     */
    public function getStepOutput(string $stepName): mixed
    {
        return $this->stepOutputs[$stepName] ?? null;
    }

    /**
     * Get all step outputs.
     *
     * @return array<string, mixed>
     */
    public function allStepOutputs(): array
    {
        return $this->stepOutputs;
    }

    /**
     * Check if a step has output.
     *
     * @param  string  $stepName  The step name.
     */
    public function hasStepOutput(string $stepName): bool
    {
        return array_key_exists($stepName, $this->stepOutputs);
    }

    /**
     * Serialize state for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'input' => $this->input,
            'metadata' => $this->metadata,
            'step_outputs' => $this->stepOutputs,
        ];
    }

    /**
     * Restore state from a serialized array.
     *
     * @param  array<string, mixed>  $data  The serialized state data.
     */
    public static function fromArray(array $data): self
    {
        $state = new self(
            input: $data['input'] ?? '',
            metadata: $data['metadata'] ?? [],
        );

        foreach ($data['step_outputs'] ?? [] as $stepName => $output) {
            $state->setStepOutput($stepName, $output);
        }

        return $state;
    }
}
