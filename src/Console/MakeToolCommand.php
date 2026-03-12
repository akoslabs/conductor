<?php

declare(strict_types=1);

namespace Conductor\Console;

use Illuminate\Console\GeneratorCommand;

final class MakeToolCommand extends GeneratorCommand
{
    /** @var string */
    protected $name = 'make:conductor-tool';

    /** @var string */
    protected $description = 'Create a new Conductor tool class';

    /** @var string */
    protected $type = 'Tool';

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return __DIR__.'/../../stubs/tool.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\Tools';
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     */
    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        $toolName = $this->getNameInput();
        $kebabName = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $toolName));

        return str_replace('{{ name }}', $kebabName, $stub);
    }
}
