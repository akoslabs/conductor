<?php

declare(strict_types=1);

namespace Conductor\Console;

use Illuminate\Console\GeneratorCommand;

final class MakeWorkflowCommand extends GeneratorCommand
{
    /** @var string */
    protected $name = 'make:conductor-workflow';

    /** @var string */
    protected $description = 'Create a new Conductor workflow class';

    /** @var string */
    protected $type = 'Workflow';

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return __DIR__.'/../../stubs/workflow.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\Workflows';
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     */
    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        $workflowName = $this->getNameInput();
        $kebabName = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $workflowName));

        return str_replace('{{ name }}', $kebabName, $stub);
    }
}
