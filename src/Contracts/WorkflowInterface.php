<?php

declare(strict_types=1);

namespace Conductor\Contracts;

use Conductor\Exceptions\WorkflowException;

interface WorkflowInterface
{
    /**
     * The unique name identifier for this workflow.
     */
    public function name(): string;

    /**
     * Define the steps of this workflow.
     *
     * @return array<string, mixed>
     */
    public function steps(): array;

    /**
     * Start a new execution of this workflow.
     *
     * @param  string  $input  The input data to pass to the workflow.
     * @param  array<string, mixed>  $metadata  Additional metadata for the workflow run.
     *
     * @throws WorkflowException
     */
    public static function start(string $input, array $metadata = []): WorkflowResultInterface;

    /**
     * Resume a paused workflow run.
     *
     * @param  string  $runId  The UUID of the workflow run to resume.
     * @param  array<string, mixed>  $data  Data to provide to the paused step.
     *
     * @throws WorkflowException
     */
    public static function resume(string $runId, array $data = []): WorkflowResultInterface;
}
