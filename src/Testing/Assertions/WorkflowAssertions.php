<?php

declare(strict_types=1);

namespace Conductor\Testing\Assertions;

use PHPUnit\Framework\Assert;

trait WorkflowAssertions
{
    /**
     * Assert that a workflow completed successfully.
     *
     * @param  string  $name  The workflow name.
     */
    public function assertWorkflowCompleted(string $name): void
    {
        Assert::assertContains(
            $name,
            $this->getCompletedWorkflows(),
            "Workflow [{$name}] did not complete.",
        );
    }

    /**
     * Assert that a specific workflow step completed.
     *
     * @param  string  $workflowName  The workflow name.
     * @param  string  $stepName  The step name.
     */
    public function assertWorkflowStepCompleted(string $workflowName, string $stepName): void
    {
        $steps = $this->getCompletedWorkflowSteps($workflowName);

        Assert::assertContains(
            $stepName,
            $steps,
            "Workflow [{$workflowName}] step [{$stepName}] did not complete.",
        );
    }

    /**
     * Get all completed workflow names.
     *
     * @return array<int, string>
     */
    abstract public function getCompletedWorkflows(): array;

    /**
     * Get completed steps for a workflow.
     *
     * @param  string  $workflowName  The workflow name.
     * @return array<int, string>
     */
    abstract public function getCompletedWorkflowSteps(string $workflowName): array;
}
