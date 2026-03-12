<?php

declare(strict_types=1);

namespace Conductor\Console;

use Illuminate\Console\GeneratorCommand;

final class MakeAgentCommand extends GeneratorCommand
{
    /** @var string */
    protected $name = 'make:conductor-agent';

    /** @var string */
    protected $description = 'Create a new Conductor agent class';

    /** @var string */
    protected $type = 'Agent';

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return __DIR__.'/../../stubs/agent.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\Agents';
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     */
    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        $agentName = $this->getNameInput();
        $kebabName = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $agentName));

        return str_replace('{{ name }}', $kebabName, $stub);
    }
}
