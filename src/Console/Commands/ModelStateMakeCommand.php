<?php

namespace Norotaro\Enumata\Console\Commands;

use Illuminate\Console\GeneratorCommand;

final class ModelStateMakeCommand extends GeneratorCommand
{
    /**
     * @var string
     */
    protected $signature = 'make:model-state {name : The Model State Name} {--N|nullable : If the State is nullable}';

    /**
     * @var string
     */
    protected $description = 'Create a new Model State class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'class';

    protected function getStub()
    {
        $file = $this->option('nullable') ? 'nullable.stub' : 'states.stub';

        return __DIR__ . "/../../../stubs/$file";
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return "{$rootNamespace}\\Models";
    }
}
